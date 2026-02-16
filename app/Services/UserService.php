<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\UserRepository;
use Exception;
use finfo;
use Hashids\Hashids;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    protected $hashids;
    protected $userRepository;
    protected $driverRepository;
    protected $carRepository;
    protected array $uploadedFiles = [];
    public function __construct(UserRepository $userRepository, DriverRepository $driverRepository, CarRepository $carRepository)
    {
        $this->userRepository = $userRepository;
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
        $this->hashids = new Hashids('secure_salt_2026_2_16', 8, '1234567890');
    }

    public function create_driver(array $request)
    {
        return DB::transaction(function () use ($request) {
            try {
                $plainPassword = Str::password(12, true, true, true, false);
                $user = $this->create_user_for_driver($request, $plainPassword);
                $driver = $this->create_driver_for_driver($request, $user->id);
                $car = $this->create_car_for_driver($request, $driver->id);
                $this->create_files_for_driver($request, $car->id, $driver->id);
                return  [
                    'status'   => true,
                    'message'  => 'تم تسجيل السائق بنجاح',
                ];;
            } catch (QueryException $e) {
                $this->rollbackFiles();
                if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'user_number')) {
                    return $this->create_driver($request);
                }
                return [
                    'status' => false,
                    'message' => 'عذراً، تعذر حفظ البيانات حالياً. يرجى التأكد من البيانات والمحاولة مرة أخرى.'
                ];
            } catch (Exception $e) {
                $this->rollbackFiles();
                return ['status' => false, 'message' => $e->getMessage()];
            }
        }, 1);
    }
    private function rollbackFiles()
    {
        foreach ($this->uploadedFiles as $filePath) {
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }
        }
    }
    private function generate_user_number()
    {

        $mtime = microtime();
        $seed = str_replace([' ', '.'], '', $mtime);
        $userNumber = $this->hashids->encode($seed);

        if (User::where('user_number', $userNumber)->exists()) {
            return $this->generate_user_number();
        }
        return $userNumber;
    }
    private function check_file($file, $id, $folder)
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
            throw new Exception("نوع الملف الحقيقي غير مسموح لـ {$file->getClientOriginalName()}");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $newName = Str::uuid()->toString() . "." . $extension;
        $path = "{$folder}/{$id}/{$newName}";

        Storage::disk('local')->put($path, file_get_contents($file));
        $this->uploadedFiles[] = $path;

        return $path;
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

        $filePath=$this->check_file($request['personal_picture'],$userId,'users');

        if (is_array($filePath)) {
            return $filePath;
        }
        $driverData['personal_picture']=$filePath;
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
    private function create_files_for_driver(array $request, $carId, $driverId)
    {
        $licensePath = $this->check_file($request['license_file'], $driverId, 'drivers');
        $this->driverRepository->create_license([
            'driver_id'    => $driverId,
            'license_file' => $licensePath
        ]);

        $unconvictedPath = $this->check_file($request['unconvicted_file'], $driverId, 'drivers');
        $this->driverRepository->create_unconvicted_paper([
            'driver_id'        => $driverId,
            'unconvicted_file' => $unconvictedPath
        ]);

        foreach ($request['car_papers'] as $paper) {
            $filePath = $this->check_file($paper['car_file'], $carId, 'cars');
            $this->carRepository->create_car_paper([
                'car_id'   => $carId,
                'type'     => $paper['type'],
                'car_file' => $filePath
            ]);
        }
    }
}
