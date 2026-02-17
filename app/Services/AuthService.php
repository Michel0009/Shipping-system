<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;
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
    $user_number = rand(10000000, 99999999);
    
    $user = $this->userRepository->create([
      'first_name' => $request['first_name'],
      'last_name' => $request['last_name'],
      'email' => $request['email'],
      'password' => $request['password'],
      'phone_number' => $request['phone_number'],
      'location' => $request['location'],
      'user_number' => $user_number,
      'role_id' => 3,
    ]);

    $this->send_email($user->email);

  }


  public function send_email(string $email)
  {
    $user = $this->userRepository->findByEmail($email);
    if (!$user) return;

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
  }


  public function verification(array $request)
  {
    $user = $this->userRepository->findByEmail($request['email']);
    if (!$user) return false;

    $cache_value = Cache::get($user->id);

    if ($cache_value && ($request['verification_code'] == $cache_value)) {

      $user->email_verified_at = now();
      $this->userRepository->save($user);

      if($user['number_of_logins'] == 0){
        $token = $user->createToken('AccessToken')->plainTextToken;
        $user->number_of_logins = $user->number_of_logins + 1;
        $this->userRepository->save($user);

        return ['token' => $token];
      }
      return true;
    
    }
    return false;
  }

  // public function new_password_verification(array $request)
  // {
  //   $user = $this->userRepository->findByEmail($request['email']);
  //   if (!$user) return false;

  //   $cache_value = Cache::get($user->id);

  //   if ($cache_value && ($request['verification_code'] == $cache_value)) {

  //     $user->email_verified_at = now();
  //     $this->userRepository->save($user);

  //     $resetToken = Str::random(64);
  //     Cache::put("reset_token_".$user->id, $resetToken, now()->addMinutes(10));
      
  //     return ['reset_token' => $resetToken];
    
  //   }
  //   return false;
  // }


  public function reset_password(array $request)
  {
    $user = $this->userRepository->findByEmail($request['email']);
    if (!$user) return false;

    $storedToken = Cache::get("reset_token_".$user->id);

    if (!$storedToken || $storedToken !== $request['reset_token']) {
        return false;
    }
    $user->password = $request['new_password'];
    $this->userRepository->save($user);

    Cache::forget("reset_token_".$user->id);
    return true;
  }


  public function login(array $request)
  {

    $user = $this->userRepository->findByEmail($request['email']);

    if ($user && Hash::check($request['password'], $user->password)) {

      if ($user['email_verified_at'] == null) {
        return 'unverified';
      }
      if ($user['status'] != 0) {
        return 'banned';
      }

      $first_login_driver = false;
      if ($user['role_id'] == 4 && $user['number_of_logins'] == 0) {
        $first_login_driver = true;
      } 

      $token = $user->createToken('AccessToken')->plainTextToken;
      $user->number_of_logins = $user->number_of_logins + 1;
      $this->userRepository->save($user);

      $responseData = [
        'message' => 'مرحباً بك',
        'token' => $token,
        'role' => $user->role->name,
        'first_login_for_driver' => $first_login_driver,
      ];

      return $responseData;
    }
    return false;
  }

  public function logout()
  {
    Auth::user()->currentAccessToken()->delete();
  }


}
