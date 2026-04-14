<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{

  protected $userRepository;

  public function __construct(UserRepository $userRepository)
  {
      $this->userRepository = $userRepository;
  }

  public function user_Register(array $request)
  {
    $user_number = app(\App\Services\UserService::class)->generate_user_number();
    
    $user = $this->userRepository->create([
      'first_name' => $request['first_name'],
      'last_name' => $request['last_name'],
      'email' => $request['email'],
      'password' => $request['password'],
      'phone_number' => $request['phone_number'],
      'user_number' => $user_number,
      'role_id' => 3,
    ]);

    $this->send_email($user->email);

  }


  public function send_email(string $email)
  {
    $user = $this->userRepository->find_by_email($email);
    if (!$user) return false;

    $code = rand(100000, 999999);

   $emailBody = '
      <div style="direction: rtl; font-family: Tahoma, Arial, sans-serif; font-size:16px; background-color:#f4f6f9; padding:20px; text-align:right; max-width:750px; margin:auto;">

          <p style="color:#1f2937; font-size:18px; margin-bottom:15px;">
              مرحباً '.$user->first_name.'،
          </p>

          <p style="margin-bottom:20px;">
              نشكرك على تسجيلك في <strong>نظام إدارة شحن البضائع</strong>، ونرحب بك في خدمتنا التي تهدف إلى تسهيل إدارة شحناتك وتتبعها.
          </p>

          <p style="margin-bottom:20px;">
              لتفعيل حسابك وإكمال عملية التسجيل بنجاح، يجب استخدام رمز التحقق الموجود في هذه الرسالة. حتى تقوم بإدخال الرمز، سيظل حسابك غير مفعل ولن تتمكن من استخدام بعض خدمات النظام.
          </p>

          <p style="font-size:32px; font-weight:bold; text-align:center; color:#2563eb; letter-spacing:5px; margin:25px 0; padding:10px; border:2px dashed #2563eb; border-radius:6px;">
              '.$code.'
          </p>

          <p style="text-align:center; margin-bottom:20px;">
              ⏳ صلاحية الرمز: 3 دقائق فقط
          </p>

          <p style="margin-top:20px; font-size:14px; color:#1f2937;">
              ملاحظة مهمة: إذا لم تقم بتأكيد حسابك خلال أسبوع، سيتم حذف الحساب تلقائيًا للحفاظ على أمان النظام وتنظيم قاعدة المستخدمين.
          </p>

          <p style="margin-top:10px; font-size:14px; color:#1f2937;">
              إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة بأمان ولن يحدث أي تغيير في حسابك.
          </p>

          <hr style="margin:25px 0; border-color:#e5e7eb;">

          <p style="font-size:14px; color:#6b7280;">
              فريق نظام إدارة شحن البضائع<br>
              نتمنى لك تجربة سلسة وآمنة مع جميع خدمات الشحن.
          </p>

      </div>
      ';

    $subject = 'نظام شحن البضائع - رمز التحقق';

    Cache::put($user->id, $code, now()->addMinutes(3));

    SendEmailJob::dispatch($user->email, $emailBody, $subject);
    return true;
  }


  public function verification(array $request)
  {
    $user = $this->userRepository->find_by_email($request['email']);
    if (!$user) return false;

    $cache_value = Cache::get($user->id);

    if ($cache_value && ($request['verification_code'] == $cache_value)) {

      $user->email_verified_at = now();
      $this->userRepository->save($user);

      if($user['number_of_logins'] == 0){
        $token = $user->createToken('AccessToken')->plainTextToken;
        $user->number_of_logins = $user->number_of_logins + 1;
        $this->userRepository->save($user);
        $refreshToken = $this->generate_refresh_token($user);

        return ['token' => $token, 'refresh_token' => $refreshToken];
      }
      return true;
    
    }
    return false;
  }

  public function new_password_verification(array $request)
  {
    $user = $this->userRepository->find_by_email($request['email']);
    if (!$user) return false;

    $cache_value = Cache::get($user->id);

    if ($cache_value && ($request['verification_code'] == $cache_value)) {

      $user->email_verified_at = now();
      $this->userRepository->save($user);

      $resetToken = Str::random(64);
      Cache::put("reset_token_".$user->id, $resetToken, now()->addMinutes(10));
      
      return ['reset_token' => $resetToken];
    
    }
    return false;
  }


  public function reset_password(array $request)
  {
    $user = $this->userRepository->find_by_email($request['email']);
    if (!$user) return false;

    $storedToken = Cache::get("reset_token_".$user->id);

    if (!$storedToken || $storedToken !== $request['reset_token']) {
        return false;
    }
    if (Hash::check($request['new_password'], $user->password)) {
        return "same_old_password";
    }
    $user->password = $request['new_password'];
    $user->number_of_change_password = $user->number_of_change_password + 1;
    $this->userRepository->save($user);

    Cache::forget("reset_token_".$user->id);
    $user->tokens()->delete();
    return true;
  }


  public function login(array $request)
  {

    $user = $this->userRepository->find_by_email($request['email']);

    if ($user && Hash::check($request['password'], $user->password)) {

      if ($user['role_id'] != 4 && $user['email_verified_at'] == null) {
        return 'unverified';
      }
      if ($user['status'] == 3) {
        return 'banned';
      }
      if ($user['status'] == 2) {
        return 'frozen';
      }

      $first_login_driver = false;
      if ($user['role_id'] == 4 && $user['number_of_change_password'] == 0) {
        $first_login_driver = true;
        $this->send_email($user->email);
      } 

      $token = $user->createToken('AccessToken')->plainTextToken;
      $user->number_of_logins = $user->number_of_logins + 1;
      $this->userRepository->save($user);
      $refreshToken = $this->generate_refresh_token($user);

      $responseData = [
        'message' => 'مرحباً بك',
        'token' => $token,
        'refresh_token' => $refreshToken,
        'role' => $user->role->name,
        'first_login_for_driver' => $first_login_driver,
      ];

      return $responseData;
    }
    return false;
  }

  public function logout()
  {
    $this->userRepository->revoke_user_tokens(auth()->id());
    Auth::user()->currentAccessToken()->delete();
  }

  private function generate_refresh_token($user)
  {
      $refreshToken = Str::random(64);

      $this->userRepository->create_refresh_token(
          $user->id,
          $refreshToken,
          now()->addMonth()
      );

      return $refreshToken;
  }

  public function refresh($refresh_token)
  {
      $refreshToken = $this->userRepository->find_refresh_token($refresh_token);

      if (
          !$refreshToken ||
          $refreshToken->is_revoked ||
          $refreshToken->expires_at->isPast()
      ) {
          return false;
      }

      $user = $this->userRepository->find_user($refreshToken->user_id);

      if ($user->status == 3) {
          return 'banned';
      }
      if ($user->status == 2) {
          return 'frozen';
      }
      // New Access 
      $newAccessToken = $user->createToken('AccessToken')->plainTextToken;
      $response = [
         'access_token' => $newAccessToken,
      ];
      // New Refresh 
      if ($refreshToken->expires_at <= now()->addDays(3)) {

        $this->userRepository->revoke_token($refreshToken);
        $newRefreshToken = $this->generate_refresh_token($user);
        $response['refresh_token'] = $newRefreshToken;
    }

      return $response;
  }


}
