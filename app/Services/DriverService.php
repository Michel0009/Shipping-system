<?php

namespace App\Services;

use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ShipmentRepository;
use App\Repositories\UserRepository;
use Exception;
use finfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverService
{

    protected $driverRepository;
    protected $carRepository;
    protected $reviewRepository;
    protected $userRepository;
    protected $shipmentRepository;
    public function __construct(
        DriverRepository $driverRepository,
        CarRepository $carRepository,
        ReviewRepository $reviewRepository,
        UserRepository $userRepository,
        ShipmentRepository $shipmentRepository
    ) {
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
        $this->reviewRepository = $reviewRepository;
        $this->userRepository = $userRepository;
        $this->shipmentRepository = $shipmentRepository;
    }
    public function change_driver_availability()
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);
        $driver->availability = !$driver->availability;
        $this->driverRepository->save($driver);
        return [
            'message' => 'تم تغيير الحالة بنجاح',
        ];
    }

    private function get_auth_driver()
    {
        $user = Auth::user();
        return $this->driverRepository->find_by_user_ID($user->id);
    }

    public function get_driver_governorates()
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->get_driver_governorates($driver);
    }

    public function get_governorates()
    {
        return $this->driverRepository->get_governorates();
    }

    public function attach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->attach_governorate($driver, $govId);
    }

    public function detach_governorate($govId)
    {
        $driver = $this->get_auth_driver();
        return $this->driverRepository->detach_governorate($driver, $govId);
    }

    public function get_available_drivers()
    {
        $shipment = Cache::get("shipment_request_user_" . auth()->id());

        if (!$shipment) {
            throw new Exception('لا يوجد طلب شحنة');
        }

        // Get available drivers who suits the shipment
        $drivers = $this->driverRepository->get_available_drivers($shipment);

        $coeff = $this->driverRepository->get_coefficients();

        $baseRate = $coeff['base_rate'] ?? 500;
        $insuranceRate = $coeff['insurance'] ?? 0.3;

        // 2. Distance between start_position and end_position
        $shipmentStart = "{$shipment['start_position_lng']},{$shipment['start_position_lat']}";
        $shipmentEnd = "{$shipment['end_position_lng']},{$shipment['end_position_lat']}";
        $url2 = "http://router.project-osrm.org/route/v1/driving/{$shipmentStart};{$shipmentEnd}?overview=false";
        $res2 = json_decode(file_get_contents($url2), true);

        if (!isset($res2['routes'][0])) {
            throw new Exception('فشل حساب مسافة الشحنة');
        }

        $shipmentDistance = $res2['routes'][0]['distance'] / 1000;

        $result = [];
        foreach ($drivers as $driver) {

            // Get driver location
            $loc = Cache::get("location_driver_{$driver->id}");
            if (!$loc) continue;

            // 1. Distance between driver and start_position
            $startDriver = "{$loc['lng']},{$loc['lat']}";

            $url1 = "http://router.project-osrm.org/route/v1/driving/{$startDriver};{$shipmentStart}?overview=false";
            $res1 = json_decode(file_get_contents($url1), true);

            if (!isset($res1['routes'][0])) continue;

            $distanceToStart = $res1['routes'][0]['distance'] / 1000;

            // Get vehicle type for price calculation
            $vehicle = $driver->car;
            $type = $vehicle->vehicle_type;

            // Get fuel details for price calculation
            $fuelType = $vehicle->fuel_type;
            $fuelPrice = $coeff[$fuelType] ?? 10000;

            // Price calculation
            $fuelCost = $shipmentDistance * ($type->avg_fuel_consumption / 100) * $fuelPrice;

            $distanceCost = $shipmentDistance * $baseRate;

            $price = $type->base_fare + $distanceCost + $fuelCost;

            $price *= $type->vehicle_coefficient;

            $w = $shipment['weight'];
            $weightFactor = $w < 50 ? 1 : ($w < 200 ? 1.2 : 1.5);
            $price *= $weightFactor;

            if ($shipment['insurance']) {
                $price += $price * $insuranceRate;
            }

            // Rates
            $rating = round($driver->reviews->avg('rate'), 2);

            $result[] = [
                'id' => $driver->id,
                'first_name' => $driver->user->first_name,
                'last_name' => $driver->user->last_name,
                'rating' => $rating,
                'vehicle' => $type->type,
                'distance_to_start_km' => round($distanceToStart, 2),
                'distance_of_shipment' => round($shipmentDistance, 2),
                'price' => round($price),
                'badge' => $driver->badge->name,
                'badge_text' => $driver->badge->text,
            ];
        }

        return collect($result)->sortBy('distance_to_start_km')->values();
    }

    public function get_driver_details($id)
    {

        $driver = $this->driverRepository->find_driver($id);
        if($driver == null) return ['message' => 'هذ السائق غير موجود'];

        $user = $this->userRepository->find_user($driver->user_id);
        $userData = [
            'user_id' => $user->id,
            'driver_id' => $id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'user_number' => $user->user_number,
            'phone_number' => $user->phone_number,
        ];

        $car = $this->carRepository->find_by_driver_ID($id);
        $driver_governorates = $this->driverRepository->get_driver_governorates($driver)
            ->makeHidden(['pivot', 'created_at', 'updated_at']);

        $average = $this->reviewRepository->get_driver_average_rate($id);
        $badge = $this->driverRepository->get_badge($driver);

        return [
            'user' => $userData,
            'car' => $car,
            'driver_governorates' => $driver_governorates,
            'average_rate' => round($average, 2),
            'badge' => $badge,
        ];
    }
    public function get_drivers()
    {
        $page = request('page', 1);
        $cacheKey = "drivers_page_" . $page;
        return Cache::tags(["driver_list"])->remember($cacheKey, 900, function () {
            $driversPaginator = $this->driverRepository->get_drivers();
            $availableCount = $this->driverRepository->get_available_drivers_count();
            return [
                'available_drivers' => $availableCount,
                'drivers'           => $driversPaginator
            ];
        });
    }
    public function get_driver_details_for_admin($id)
    {
        $ttl = 86400;

        $driver =   Cache::remember(
            "driver_{$id}_driver",
            $ttl,
            function () use ($id) {
                return $this->driverRepository->find_driver($id);
            }
        );
        $user = Cache::remember("driver_{$id}_user", $ttl, function () use ($driver) {
            return $this->userRepository->find_user($driver->user_id);
        });
        $car = Cache::remember("driver_{$id}_car", $ttl, function () use ($id) {
            return $this->carRepository->find_by_driver_ID($id);
        });
        $files = Cache::remember("driver_{$id}_docs", $ttl, function () use ($car, $driver) {
            $carFiles = $this->carRepository->get_car_files($car);
            $driverFiles = $this->driverRepository->get_driver_files($driver);
            return [
                'car_files' => $carFiles,
                'driver_files' => $driverFiles
            ];
        });
        $driver_governorates = $this->driverRepository->get_driver_governorates($driver)
            ->makeHidden(['pivot', 'created_at', 'updated_at']);

        $average_rate = Cache::remember("driver_{$id}_avg_rate", 600, function () use ($id) {
            return $this->reviewRepository->get_driver_average_rate($id);
        });
        $badge =  $this->driverRepository->get_badge($driver);
        $shipments = Cache::remember("driver_{$id}_shipments", 600, function () use ($id) {
            return $this->shipmentRepository->get_shipments_by_driver_id($id);
        });
        if ($shipments->isNotEmpty()) {
            $amountToPay = $shipments->sum('price');
            $amountToPay *= 0.15;
        } else {
            $amountToPay = 0;
        }
        return [
            'driver' => $driver,
            'user' => $user,
            'car' => $car,
            'files' => $files,
            'driver_governorates' => $driver_governorates,
            'average_rate' => round($average_rate, 2),
            'badge' => $badge,
            'amount_to_pay' => $amountToPay
        ];
    }
    public function search_for_driver(array $data)
    {
        $driver_number = $data['driver_number'];
        $driver = $this->driverRepository->find_by_user_number($driver_number);
        if (!$driver) {
            return [
                'message' => 'لا يوجد سائق بهذا الرقم'
            ];
        }
        return $driver;
    }
    private function check_files(array $mainFiles, array $carPapers, $userId)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $allowedMime = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'image/heic',
            'image/heif',
            'image/heic-sequence',
            'image/heif-sequence'
        ];
        $allFilesToValidate = array_merge(
            $mainFiles,
            array_column($carPapers, 'car_file')
        );
        foreach ($allFilesToValidate as $file) {
            if ($file) {
                $realMime = $finfo->file($file->getPathname());
                if (!in_array($realMime, $allowedMime)) {
                    throw new Exception("{$file->getClientOriginalName()} نوع الملف الحقيقي غير مسموح لـ ", 422);
                }
            }
        }
        $storedMain = [];
        foreach ($mainFiles as $key => $file) {
            if ($file) {
                $extension = strtolower($file->getClientOriginalExtension());
                $newName = Str::uuid()->toString() . "." . $extension;
                $path = "users/{$userId}/{$newName}";
                Storage::disk('local')->put($path, file_get_contents($file));
                $storedMain[$key] = $path;
            }
        }
        $storedCarPapers = [];
        foreach ($carPapers as $paper) {
            if (isset($paper['car_file'])) {
                $file = $paper['car_file'];
                $extension = strtolower($file->getClientOriginalExtension());
                $newName = Str::uuid()->toString() . "." . $extension;
                $path = "users/{$userId}/{$newName}";

                Storage::disk('local')->put($path, file_get_contents($file));

                $storedCarPapers[] = [
                    'type' => $paper['type'],
                    'car_file' => $path
                ];
            }
        }
        return [$storedMain, $storedCarPapers];
    }
    private function deleteFiles($paths)
    {
        if (empty($paths)) {
            return;
        }

        $filesToDelete = is_array($paths) ? $paths : [$paths];

        foreach ($filesToDelete as $path) {
            if ($path && Storage::disk('local')->exists($path)) {
                Storage::disk('local')->delete($path);
            }
        }
    }
    public function update_driver($id, array $data)
    {
        $userFields = ['first_name', 'last_name', 'phone_number'];

        $driverFields = [
            'father_name',
            'mother_name',
            'mother_last_name',
            'birth_date',
            'birth_place',
            'national_number',
            'governorate',
            'city',
            'neighborhood',
            'gender',
            'additional_phone_number',
            'nationality',
        ];
        $carFields = [
            'vehicle_type_id',
            'license_plate_number',
            'manufacturer',
            'model',
            'year_of_manufacture',
            'color',
            'fuel_type',
            'car_status'
        ];
        $files = [
            'unconvicted_file',
            'license_file',
            'personal_picture'
        ];
        $carPapersData = collect($data['car_papers'] ?? [])->filter()->toArray();
        $validData = collect($data)
            ->only(array_merge($userFields, $driverFields, $carFields, $files))
            ->filter(function ($value) {
                return !is_null($value) && $value !== '';
            })
            ->toArray();
        if (empty($validData) && empty($carPapersData) && empty($personalPicture)) {
            return [
                'status' => false,
                'message' => 'لا يوجد بيانات لتحديثها',
                'code' => 422
            ];
        }

        $files = array_intersect_key($data, array_flip($files));
        $driver = $this->driverRepository->find_driver($id);

        $storedFiles = [];
        $storedCarPapers = [];

        if (!empty($files) || !empty($carPapersData)) {
            [$storedFiles, $storedCarPapers] = $this->check_files($files, $carPapersData, $driver->user_id);
        }

        $userData = array_intersect_key($data, array_flip($userFields));
        $driverData = array_intersect_key($data, array_flip($driverFields));
        $carData = array_intersect_key($data, array_flip($carFields));
        try {
            return DB::transaction(function () use ($driver, $userData, $driverData, $carData, $storedFiles, $storedCarPapers, $id) {
                $pathsToDelete = [];
                if (!empty($driverData)) {
                    $this->driverRepository->update($id, $driverData);
                }
                if (!empty($userData)) {
                    $this->userRepository->update($driver->user_id, $userData);
                }
                if (!empty($carData)) {
                    $car = $this->carRepository->find_by_driver_ID($id);
                    $this->carRepository->update($car->id, $carData);
                }
                foreach ($storedFiles as $type => $path) {
                    if ($type === "license_file") {
                        $pathsToDelete[] = $this->driverRepository->get_license_path($id);
                        $this->driverRepository->update_license($id, ['license_file' => $path]);
                    }
                    if ($type === "unconvicted_file") {
                        $pathsToDelete[] = $this->driverRepository->get_unconvicted_paper_path($id);
                        $this->driverRepository->update_unconvicted_paper($id, ['unconvicted_file' => $path]);
                    }
                    if ($type === "personal_picture") {
                        $pathsToDelete[] = $this->driverRepository->find_driver($id)->personal_picture;
                        $this->driverRepository->update($id, ['personal_picture' => $path]);
                    }
                }
                foreach ($storedCarPapers as $paper) {
                    $carPapers = $this->carRepository->get_car_files($driver->car);
                    $existingPaper = $carPapers->firstWhere('type', $paper['type']);
                    $pathsToDelete[] = $existingPaper->car_file ?? null;
                    if ($existingPaper) {
                        $this->carRepository->update_car_paper($existingPaper->id, ['car_file' => $paper['car_file']]);
                    } else if (
                        ($paper['type'] === 'ملكية' && $oppositePaper = $carPapers->firstWhere('type', 'اجار')) ||
                        ($paper['type'] === 'اجار' && $oppositePaper = $carPapers->firstWhere('type', 'ملكية'))
                    ) {
                        $pathsToDelete[] = $oppositePaper->car_file;
                        $this->carRepository->update_car_paper($oppositePaper->id, [
                            'type' => $paper['type'],
                            'car_file' => $paper['car_file']
                        ]);
                    }
                    else {
                        $this->carRepository->create_car_paper([
                            'car_id' => $driver->car->id,
                            'type' => $paper['type'],
                            'car_file' => $paper['car_file']
                        ]);
                    }
                }
                $this->deleteFiles($pathsToDelete);
                return [
                    'status' => true,
                    'message' => 'تم تعديل السائق بنجاح',
                    'code' => 200
                ];
            });
        } catch (Exception $e) {
            $this->deleteFiles(array_merge($storedFiles, array_column($storedCarPapers, 'car_file')));
            if ($e->getCode() == 422) {
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                    'code' => 422
                ];
            }
            throw $e;
        }
    }
    public function get_driver_image($id)
    {
        $driver = $this->driverRepository->find_driver($id);

        if (!$driver) {
            return response()->json([
                'message' => 'السائق غير موجود'
            ], 404);
        }

        $path = storage_path('app/private/' . $driver->personal_picture);

        if (!file_exists($path)) {
            return response()->json([
                'message' => 'الصورة غير موجودة'
            ], 404);
        }

        return response()->file($path);
    }

    public function count_continuous_successful_shipments(){

        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);

        return $driver->continuous_successful_shipments;
    }

    public function set_driver_location(array $data)
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);

        Cache::put("location_driver_{$driver->id}", [
            'lat' => $data['lat'],
            'lng' => $data['lng']
        ], now()->addHours(1));

        return true;
    }
}
