<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Models\User;
use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;
use Exception;
use finfo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    protected $userRepository;
    protected $driverRepository;
    protected $carRepository;
    public function __construct(UserRepository $userRepository, DriverRepository $driverRepository, CarRepository $carRepository)
    {
        $this->userRepository = $userRepository;
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
    }

    public function create_driver(array $request)
    {
        return DB::transaction(function () use ($request) {
            $userId = null;
            try {
                $plainPassword = Str::password(12, true, true, true, false);
                $user = $this->create_user_for_driver($request, $plainPassword);
                $userId = $user->id;
                $driver = $this->create_driver_for_driver($request, $userId);
                $car = $this->create_car_for_driver($request, $driver->id);
                $this->create_files_for_driver($request, $car->id, $driver->id, $userId);
                $this->driverRepository->attach_governorates($driver, $request['governorate_ids']);
                $this->send_email($user->email, $plainPassword);
                return [
                    'status' => true,
                    'message' => 'تم تسجيل السائق بنجاح',
                    'code' => 201
                ];
            } catch (Exception $e) {
                if ($userId) {
                    $this->rollbackFolder($userId);
                }
                throw $e;
            }
        }, 3);
    }
    private function rollbackFolder($userId)
    {
        $directory = "users/{$userId}";
        if (Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->deleteDirectory($directory);
        }
    }
    public function generate_user_number()
    {
        $lastId = User::max('id');
        $userNumber = strrev(str_pad($lastId, 8, '0', STR_PAD_LEFT));
        if (User::where('user_number', $userNumber)->exists()) {
            return $this->generate_user_number();
        }
        return $userNumber;
    }
    private function check_file($file, $userId)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file->getPathname());

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'image/heic',
            'image/heif',
            'image/heic-sequence',
            'image/heif-sequence'
        ];

        if (!in_array($realMime, $allowedMime)) {
            throw new Exception("نوع الملف الحقيقي غير مسموح لـ {$file->getClientOriginalName()}", 422);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $newName = Str::uuid()->toString() . "." . $extension;

        $path = "users/{$userId}/{$newName}";

        Storage::disk('local')->put($path, file_get_contents($file));

        return $path;
    }
    private function send_email(string $email, string $plainPassword)
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) return;

        $subject = 'مرحباً بك في نظام شحن البضائع - بيانات الدخول الخاصة بك';

        $emailBody = '
      <div style="direction: rtl; font-family: Tahoma, Arial, sans-serif; font-size:16px; background-color:#f4f6f9; padding:20px; text-align:right; max-width:750px; margin:auto; border-radius:10px;">

          <div style="text-align:center; margin-bottom:20px;">
              <h2 style="color:#2563eb;">نظام إدارة شحن البضائع</h2>
          </div>

          <p style="color:#1f2937; font-size:18px; margin-bottom:15px;">
              مرحباً <strong>' . $user->first_name . '</strong>،
          </p>

          <p style="margin-bottom:20px; line-height:1.6;">
              يسعدنا انضمامك إلينا في <strong>نظام إدارة شحن البضائع</strong>. تم إنشاء حسابك بنجاح، ويمكنك الآن البدء في استقبال وإدارة طلبات الشحن الخاصة بك.
          </p>

          <div style="background-color:#ffffff; padding:20px; border-radius:8px; border:1px solid #e5e7eb; margin-bottom:20px;">
              <p style="margin-top:0; font-weight:bold; color:#374151;">بيانات تسجيل الدخول:</p>
              <p style="margin:10px 0;">البريد الإلكتروني: <span style="color:#2563eb; font-weight:bold;">' . $user->email . '</span></p>
              <p style="margin:10px 0;">كلمة المرور المؤقتة:</p>
              <div style="font-size:24px; font-weight:bold; text-align:center; color:#ffffff; background-color:#2563eb; padding:15px; border-radius:6px; margin:15px 0;">
                  ' . $plainPassword . '
              </div>
          </div>

          <p style="margin-bottom:20px; color:#ef4444; font-size:14px; font-weight:bold;">
              ⚠️ ملاحظة أمنية: يرجى تسجيل الدخول وتغيير كلمة المرور هذه فوراً عند أول استخدام للحفاظ على أمان حسابك.
          </p>

          <p style="margin-top:20px; font-size:14px; color:#1f2937;">
              بإمكانك الآن تحميل التطبيق وتسجيل الدخول لمباشرة العمل. إذا واجهت أي صعوبة، لا تتردد في التواصل مع فريق الدعم الفني.
          </p>

          <hr style="margin:25px 0; border: 0; border-top:1px solid #e5e7eb;">

          <p style="font-size:14px; color:#6b7280; text-align:center;">
              فريق نظام إدارة شحن البضائع<br>
              نتمنى لك رحلات آمنة وموفقة.
          </p>

      </div>
      ';

        SendEmailJob::dispatch($user->email, $emailBody, $subject);
    }
    private function create_user_for_driver(array $request, $password)
    {
        $userData = [
            'user_number'  => $this->generate_user_number(),
            'role_id'      => 4,
            'first_name'   => $request['first_name'],
            'last_name'    => $request['last_name'],
            'email'        => $request['email'],
            'phone_number' => $request['phone_number'],
            'location'     => $request['location'],
            'password'     => Hash::make($password),
        ];

        return $this->userRepository->create($userData);
    }
    private function create_driver_for_driver(array $request, $userId)
    {
        $driverData = [
            'user_id'          => $userId,
            'badge_id'         => 1,
            'father_name'      => $request['father_name'],
            'mother_name'      => $request['mother_name'],
            'mother_last_name' => $request['mother_last_name'],
            'birth_date'       => $request['birth_date'],
            'birth_place'      => $request['birth_place'],
            'national_number'  => $request['national_number'],
            'governorate'      => $request['governorate'],
            'city'             => $request['city'],
            'neighborhood'     => $request['neighborhood'],
            'gender'           => $request['gender'],
            'nationality'      => $request['nationality'],
        ];
        if (isset($request['additional_phone_number'])) {
            $driverData['additional_phone_number'] = $request['additional_phone_number'];
        }

        $filePath = $this->check_file($request['personal_picture'], $userId);

        if (is_array($filePath)) {
            return $filePath;
        }
        $driverData['personal_picture'] = $filePath;
        return $this->driverRepository->create($driverData);
    }
    private function create_car_for_driver(array $request, $driverId)
    {
        $carData = [
            'driver_id'            => $driverId,
            'vehicle_type_id'      => $request['vehicle_type_id'],
            'license_plate_number' => $request['license_plate_number'],
            'manufacturer'         => $request['manufacturer'],
            'model'                => $request['model'],
            'year_of_manufacture'  => $request['year_of_manufacture'],
            'color'                => $request['color'],
            'fuel_type'            => $request['fuel_type'],
            'car_status'           => $request['car_status']
        ];
        return $this->carRepository->create($carData);
    }
    private function create_files_for_driver(array $request, $carId, $driverId, $userId)
    {
        $licensePath = $this->check_file($request['license_file'], $userId);
        $this->driverRepository->create_license([
            'driver_id'    => $driverId,
            'license_file' => $licensePath
        ]);

        $unconvictedPath = $this->check_file($request['unconvicted_file'], $userId);
        $this->driverRepository->create_unconvicted_paper([
            'driver_id'        => $driverId,
            'unconvicted_file' => $unconvictedPath
        ]);

        foreach ($request['car_papers'] as $paper) {
            $filePath = $this->check_file($paper['car_file'], $userId);
            $this->carRepository->create_car_paper([
                'car_id'   => $carId,
                'type'     => $paper['type'],
                'car_file' => $filePath
            ]);
        }
    }
}
