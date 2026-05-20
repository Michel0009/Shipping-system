<?php

namespace App\Services;

use App\Repositories\CarRepository;
use App\Repositories\DriverRepository;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostService
{
    protected $postRepository;
    protected $userRepository;
    protected $driverRepository;
    protected $carRepository;

    public function __construct(PostRepository $postRepository, UserRepository $userRepository,
            DriverRepository $driverRepository, CarRepository $carRepository)
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->driverRepository = $driverRepository;
        $this->carRepository = $carRepository;
    }

    public function create_post(array $data)
    {
        return DB::transaction(function () use ($data) {
            $distance = $this->calculate_OSRM_distance(
                $data['start_position_lng'],
                $data['start_position_lat'],
                $data['end_position_lng'],
                $data['end_position_lat']
            );

            $suitableVehicles = $this->carRepository->get_suitable_vehicles($data);

            $fuelPrices = $this->carRepository->get_fuel_prices();
            $coeffs = $this->carRepository->get_coefficients_calculation();
            
            $minFuel = min($fuelPrices);
            $maxFuel = max($fuelPrices);
            $baseRate = $coeffs['base_rate'];
            $insuranceRate = $coeffs['insurance'] ?? 0.3;

            $minVehCoeff = $suitableVehicles->min('vehicle_coefficient');
            $maxVehCoeff = $suitableVehicles->max('vehicle_coefficient');
            
            $minBaseFare = $suitableVehicles->min('base_fare');
            $maxBaseFare = $suitableVehicles->max('base_fare');
            
            $minCons = $suitableVehicles->min('avg_fuel_consumption') / 100;
            $maxCons = $suitableVehicles->max('avg_fuel_consumption') / 100;

            $distanceCost = $distance * $baseRate;
            $w = $data['weight'];
            $weightFactor = $w < 50 ? 1 : ($w < 200 ? 1.2 : 1.5);

            $minPrice = ($minBaseFare + $distanceCost + ($distance * $minCons * $minFuel)) * $minVehCoeff * $weightFactor;
            
            $maxPrice = ($maxBaseFare + $distanceCost + ($distance * $maxCons * $maxFuel)) * $maxVehCoeff * $weightFactor;

            if ($data['insurance']) {
                $minPrice *= (1 + $insuranceRate);
                $maxPrice *= (1 + $insuranceRate);
            }

            $data['min_price'] = round($minPrice);
            $data['max_price'] = round($maxPrice);
            $data['user_id'] = auth()->id();

            $post = $this->postRepository->create($data);

            return $post;
        });
    }

    public function update_prices(array $prices)
    {
        $post = $this->postRepository->find($prices['post_id']);

        if ($post->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بتعديل هذا الإعلان');
        }
        if ($prices['min_price'] < $post->min_price) {
            abort(422, "الحد الأدنى لا يمكن أن يكون أقل من السعر المقترح ({$post->min_price})");
        }
        if ($prices['max_price'] > $post->max_price) {
            abort(422, "الحد الأعلى لا يمكن أن يكون أعلى من السعر المقترح ({$post->max_price})");
        }
        $prices_reange = [
            'min_price' => $prices['min_price'],
            'max_price' => $prices['max_price'],
        ];

        return $this->postRepository->update($post, $prices_reange);
    }

    public function delete_post($postId)
    {
        $post = $this->postRepository->find($postId);

        if ($post->user_id !== auth()->id()) {
            abort(403, 'غير مصرح لك بحذف هذا الإعلان');
        }

        return $this->postRepository->delete($post);
    }

    public function get_my_posts()
    {
        $userId = auth()->id();
        return Cache::remember('user_posts_' . $userId, now()->addHours(24), function () use ($userId) {
            return $this->postRepository->get_user_posts($userId);
        });
    }

    private function calculate_OSRM_distance($lng1, $lat1, $lng2, $lat2)
    {
        $url = "http://router.project-osrm.org/route/v1/driving/{$lng1},{$lat1};{$lng2},{$lat2}?overview=false";
        try {
            $response = json_decode(file_get_contents($url), true);
            return $response['routes'][0]['distance'] / 1000;
        } catch (\Exception $e) {
            abort(400, 'حدثت مشكلة أثناء حساب المسافة الرجاء إعادة المحاولة');
        }
    }

    public function apply_to_post(array $data)
    {
        $driver = $this->driverRepository->find_by_user_ID(auth()->id());
        $post = $this->postRepository->find($data['post_id']);

        if ($post->finished) {
            return ['message' => 'عذراً، هذا الإعلان مغلق ومكتمل ولا يمكن التقديم عليه.', 'code' => 422];
        }

        if ($data['price'] < $post->min_price || $data['price'] > $post->max_price) {
            return ['message' => "يجب أن يكون السعر ضمن النطاق المقترح بين {$post->min_price} و {$post->max_price}.", 'code' => 422];
        }

        if (strtotime($data['date']) > strtotime($post->last_date)) {
            return ['message' => 'تاريخ التوصيل لا يمكن أن يتجاوز تاريخ انتهاء الإعلان.', 'code' => 422];
        }

        $this->postRepository->attach_driver($post, $driver->id, [
            'price' => $data['price'],
            'date' => $data['date']
        ]);
        $data_body = [
            'post_id' => $post->id,
        ];
        app(\App\Services\NotificationService::class)->send_notification(
            $post->user_id,
            "تم إرسال طلب نقل شحنة من قبل أحد السائقين لإيصال شحنتك التي قمت بنشرها سابقا.",
            $post->id,
            'إعلانات غير فورية',
            $data_body
        );

        return ['message' => 'تم تقديم عرضك على هذه الشحنة بنجاح.', 'code' => 200];
    }

    public function cancel_application($postId)
    {
        $driver = $this->driverRepository->find_by_user_ID(auth()->id());
        $post = $this->postRepository->find($postId);

        if ($post->finished) {
            return ['message' => 'لا يمكنك إلغاء العرض، الإعلان منتهي ومغلق بالفعل.', 'code' => 422];
        }

        $this->postRepository->detach_driver($post, $driver->id);

        return ['message' => 'تم سحب وإلغاء عرضك بنجاح.', 'code' => 200];
    }

    public function get_post_details($postId)
    {
        $user = auth()->user();

        $post = $this->postRepository->get_post_details($postId);
        $post = $this->transform_single_post($post);

        if ($user->role_id == 3 && $post->user_id == $user->id) {

            $formattedDrivers = $post->drivers->map(function ($driver) {
                $vehicle_type = $driver->car->vehicle_type;
                $rating = round($driver->reviews->avg('rate'), 2);

                return [
                    'id' => $driver->id,
                    'first_name' => $driver->user->first_name,
                    'last_name' => $driver->user->last_name,
                    'rating' => $rating,
                    'vehicle' => $vehicle_type->type,
                    'date' => $driver->pivot->date,
                    'price' => $driver->pivot->price,
                    'badge' => $driver->badge->name,
                    'badge_text' => $driver->badge->text,
                ];
            });
            $postData = $post->toArray();
            $postData['drivers'] = $formattedDrivers;

            return $postData;
        }
        return $post->makeHidden(['drivers']);
    }

    private function transform_single_post($post)
    {
        $start = $post->governorates
            ->where('pivot.start_end', 'start')
            ->first();
        $post['start_governorate'] = $start?->name;

        $end = $post->governorates
            ->where('pivot.start_end', 'end')
            ->first();
        $post['end_governorate'] = $end?->name;

        return $post->makeHidden(['governorates']);
    }

    public function get_suitable_posts_for_driver()
    {
        $driver = $this->driverRepository->get_driver_with_vehicle_and_governorates(auth()->id());
        $driverGovIds = $driver->governorates->pluck('id')->toArray();

        $posts = $this->postRepository->get_available_posts_for_vehicle($driver->car->vehicle_type, $driverGovIds);

        return $posts;
    }

    // public function choose_driver_for_post(array $data)
    // {
    //     $userId = Auth::id();

    //     $postId = $data['post_id'];
    //     $driverId = $data['driver_id'];
    //     $post = $this->postRepository->find_post_for_assignment($postId, $userId);

    //     $acceptedDriver = $post->drivers->firstWhere('id', $driverId);

    //     if (!$acceptedDriver->pivot) {
    //         abort(404, 'هذا السائق لم يتقدم بعرض على هذا المنشور');
    //     }

    //     $driverProposedDate = $acceptedDriver->pivot->proposed_date; 
    //     $deliveryDeadline = Carbon::parse($driverProposedDate)->addDay()->endOfDay();
        
    //     $finalPrice = $acceptedDriver->pivot->price;
    //     $shipment = $this->postRepository->convertPostToShipment($post, $driverId, $finalPrice, $deliveryDeadline);

    //     $this->sendOffersNotifications($post, $driverId);

    //     return $shipment;
    // }

    // protected function sendOffersNotifications($post, int $acceptedDriverId)
    // {
    //     foreach ($post->drivers as $driver) {
            // if ($driver->id == $acceptedDriverId) {
            //     app(\App\Services\NotificationService::class)->send_notification(
            //         $driver->id,
            //         'تم قبول عرضك المالي!',
            //         "مبروك، لقد وافق الشاحن على عرضك لنقل البضاعة الخاصة بالطلب رقم (#{$post->id}). يرجى مراجعة الشحنات الجارية لمباشرة العمل.",
            //         ['shipment_id' => $post->id, 'status' => 'accepted']
            //     );
            // } else {
            //     app(\App\Services\NotificationService::class)->send_notification(
            //         $driver->id,
            //         'تحديث بشأن إعلان الشحن',
            //         "نشكرك على تقديم عرضك للمنشور رقم (#{$post->id})، نعتذر منك فقد تم اختيار سائق آخر يتناسب مع متطلبات الشحنة الحالية. بالتوفيق في المرات القادمة!",
            //         ['post_id' => $post->id, 'status' => 'rejected']
            //     );
            // }
    //     }
    // }

}
