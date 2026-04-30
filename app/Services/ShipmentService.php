<?php

namespace App\Services;

use App\Repositories\DriverRepository;
use App\Repositories\ShipmentRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ShipmentService
{

    protected $driverRepository;
    protected $shipmentRepository;

    public function __construct(DriverRepository $driverRepository, ShipmentRepository $shipmentRepository)
    {
        $this->driverRepository = $driverRepository;
        $this->shipmentRepository = $shipmentRepository;
    }

    public function create_shipment(array $data)
    {
        $user = Auth::user();
        $cacheKey = "shipment_request_user_" . $user->id;
        $expiresAt = now()->addHour();

        $payload = [
            'user_id' => $user->id,
            'weight' => $data['weight'],
            'height' => $data['height'],
            'width' => $data['width'],
            'length' => $data['length'],
            'object' => $data['object'],
            'insurance' => $data['insurance'],
            'start_position_lat' => $data['start_position_lat'],
            'start_position_lng' => $data['start_position_lng'],
            'end_position_lat' => $data['end_position_lat'],
            'end_position_lng' => $data['end_position_lng'],
            'start_governorate_id' => $data['start_governorate_id'],
            'end_governorate_id' => $data['end_governorate_id'],
            'expires_at' => $expiresAt
        ];

        $added = Cache::add($cacheKey, $payload, $expiresAt);
        if (!$added) {
            abort(409, 'لديك طلب شحنة قيد المعالجة بالفعل');
        }

        return $payload;
    }

    public function get_shipment_request()
    {
        $user = Auth::user();

        $cacheKey = "shipment_request_user_" . $user->id;
        $shipment = Cache::get($cacheKey);

        if (!$shipment) {
            abort(404, 'لا يوجد طلب شحنة حالياً');
        }
        return $shipment;
    }

    public function delete_shipment_request()
    {
        $user = Auth::user();

        $cacheKey = "shipment_request_user_" . $user->id;

        if (!Cache::has($cacheKey)) {
            abort(404, 'لا يوجد طلب شحنة لحذفه');
        }
        Cache::forget($cacheKey);

        return true;
    }

    public function update_shipment_request(array $data)
    {
        $user = Auth::user();

        $cacheKey = "shipment_request_user_" . $user->id;
        $shipment = Cache::get($cacheKey);

        if (!$shipment) {
            abort(404, 'لا يوجد طلب شحنة لتعديله');
        }

        $updatedShipment = array_merge($shipment, $data);

        Cache::put($cacheKey, $updatedShipment, $shipment['expires_at']);

        return $updatedShipment;
    }

    public function extend_shipment_request()
    {
        $cacheKey = "shipment_request_user_" . auth()->id();

        $shipment = Cache::get($cacheKey);

        if (!$shipment) {
            abort(404, 'لا يوجد طلب شحنة');
        }

        $expiresAt = $shipment['expires_at'];

        // الوقت المتبقي
        $remaining = now()->diffInSeconds($expiresAt);

        // تمديد ساعة
        $newExpiresAt = now()->addHour();

        $shipment['expires_at'] = $newExpiresAt;

        Cache::put($cacheKey, $shipment, $newExpiresAt);

        return [
            'remaining_minutes_before' => round($remaining / 60),
            'remaining_minutes_after' => 60
        ];
    }

    public function send_to_driver(array $data)
    {
        $user = Auth::user();

        $shipmentKey = "shipment_request_user_" . $user->id;
        $shipment = Cache::get($shipmentKey);

        if (!$shipment) {
            abort(404, 'لا يوجد طلب شحنة');
        }
        $driver = $this->driverRepository->find_driver($data['driver_id']);

        if (!$driver->availability) {
            return [
                'message' => "هذا السائق غير متاح حاليا",
                'status_code' => 401
            ];
        }

        $startGov = $this->driverRepository->find_governorate($shipment['start_governorate_id']);
        $endGov = $this->driverRepository->find_governorate($shipment['end_governorate_id']);
        $shipment['start_governorate'] = $startGov->name;
        $shipment['end_governorate'] = $endGov->name;

        $userRequestPattern = "*driver_request_*_user_{$user->id}";
        $existingRequests = collect(Cache::getRedis()->keys($userRequestPattern));

        if ($existingRequests->isNotEmpty()) {
            return [
                'message' => "لديك طلب قيد الانتظار مع سائق آخر",
                'status_code' => 409
            ];
        }

        $requestKey = "driver_request_{$driver->id}_user_{$user->id}";
        $expiresAt = now()->addMinutes(10);

        $payload = [
            'user_id' => $user->id,
            'driver_id' => $driver->id,
            'price' => $data['price'],
            'distance_to_start' => $data['distanceToStart'],
            'shipment_distance' => $data['shipmentDistance'],
            'shipment' => $shipment,
            'expires_at' => $expiresAt
        ];

        $added = Cache::add($requestKey, $payload, $expiresAt);

        if (!$added) {
            // Cache::forget($requestKey);
            return [
                'message' => "تم إرسال طلب لهذا السائق مسبقاً",
                'status_code' => 401
            ];
        }

        app(\App\Services\NotificationService::class)->send_notification(
            $driver->user_id,
            'لديك طلب شحنة جديد. الرجاء التحقق منه والتعامل معه',
            0,
            'طلب شحنة جديد',
            $payload
        );

        return [
            'message' => "تم إرسال الطلب إلى السائق",
            'status_code' => 200
        ];
    }


    public function respond_to_request(array $data)
    {
        $user = Auth::user();
        $driver =  $this->driverRepository->find_by_user_ID($user->id);

        $requestKey = "driver_request_{$driver->id}_user_{$data['user_id']}";
        $request = Cache::get($requestKey);

        if (!$request) {
            abort(404, 'انتهت صلاحية الطلب أو أنه غير موجود');
        }

        if (!$data['action']) {

            Cache::forget($requestKey);

            app(\App\Services\NotificationService::class)->send_notification(
                $data['user_id'],
                'تم رفض طلب الشحنة الذي قمت بإرساله من طرف السائق.',
                0,
                'رفض الطلب',
                []
            );

            return "تم رفض الطلب";
        }
        $shipmentNumber = time() . rand(100, 999);
        $pin = random_int(100000, 999999);
        $qrPin = Str::uuid()->toString();

        $shipment = $this->shipmentRepository->create(
            $request,
            $shipmentNumber,
            $pin,
            $qrPin
        );

        Cache::forget($requestKey);
        Cache::forget("shipment_request_user_" . $data['user_id']);

        $data_body = [
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'status' => $shipment->status
        ];
        app(\App\Services\NotificationService::class)->send_notification(
            $data['user_id'],
            "تم قبول طلب الشحنة خاصتك. يمكنك متابعتها الآن باستخدام رقم الشحنة {$shipmentNumber} المولّد.",
            $shipment->id,
            'قبول الطلب',
            $data_body
        );

        return "تم قبول الطلب وإنشاء الشحنة";
    }

    public function confirm_pickup(array $data)
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);

        $shipment = $this->shipmentRepository->find_shipment($data['shipment_id']);

        if ($shipment->driver_id !== $driver->id) {
            return [
                'message' => "غير مصرح لك بهذه الشحنة",
                'status_code' => 403
            ];
        }

        if ($shipment->qr_pin !== $data['qr_pin']) {
            return [
                'message' => "الرمز غير صحيح",
                'status_code' => 401
            ];
        }

        if ($shipment->status !== 'جارية') {
            return [
                'message' => "لا يمكن تأكيد الاستلام في هذه الحالة",
                'status_code' => 401
            ];
        }

        $shipment->status = 'قيد التوصيل';
        $this->shipmentRepository->save($shipment);

        return [
            'message' => "تم استلام الشحنة وبدء التوصيل",
            'status_code' => 200
        ];
    }

    public function confirm_delivery(array $data)
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);

        $shipment = $this->shipmentRepository->find_shipment($data['shipment_id']);

        if ($shipment->driver_id !== $driver->id) {
            return [
                'message' => "غير مصرح لك بهذه الشحنة",
                'status_code' => 403
            ];
        }

        if ($shipment->pin !== $data['pin']) {
            return [
                'message' => "رمز التحقق غير صحيح",
                'status_code' => 401
            ];
        }

        if ($shipment->status !== 'قيد التوصيل' && $shipment->status !== 'جارية') {
            return [
                'message' => "لا يمكن تأكيد الاستلام في هذه الحالة",
                'status_code' => 401
            ];
        }

        if (now()->greaterThan($shipment->delivery_deadline)) {
            return [
                'message' => "انتهت مهلة تأكيد التسليم",
                'status_code' => 401
            ];
        }

        $shipment->status = 'مستلمة';
        $shipment->success = true;
        $this->shipmentRepository->save($shipment);

        $driver->continuous_successful_shipments = $driver->continuous_successful_shipments + 1;
        $this->driverRepository->save($driver);

        $data_body = [
            'id' => $shipment->id,
            'shipment_number' => $shipment->shipment_number,
            'status' => $shipment->status
        ];
        app(\App\Services\NotificationService::class)->send_notification(
            $shipment->user_id,
            "تم تأكيد استلام الشحنة ذات الرقم {$shipment->shipment_number} بنجاح.. يمكنك الآن تقييم خدمة السائق بإيصال شحنتك.",
            $shipment->id,
            'تأكيد استلام الشحنة',
            $data_body
        );

        return [
            'message' => "تم تسليم الشحنة بنجاح",
            'status_code' => 200
        ];
    }

    public function get_shipments()
    {
        $page = request('page', 1);
        $cacheKey = "shipments_page_" . $page;

        return Cache::tags(['shipments_all'])->remember($cacheKey, 900, function () {
            return $this->shipmentRepository->get_shipments();
        });
    }

    public function get_shipments_for_user()
    {
        $user = Auth::user();
        $user_id = $user->id;
        $page = request('page', 1);
        $cacheKey = "user_{$user_id}_shipments_page_" . $page;

        return Cache::tags(['shipments_user_' . $user_id])->remember($cacheKey, 900, function () use ($user_id) {
            return $this->shipmentRepository->get_shipments_for_user($user_id);
        });
    }

    public function get_shipments_for_driver()
    {
        $user = Auth::user();
        $driver = $this->driverRepository->find_by_user_ID($user->id);
        return $this->get_shipments_by_driver_id($driver->id);
    }

    public function get_shipments_by_driver_id($driver_id)
    {
        $page = request('page', 1);
        $cacheKey = "driver_{$driver_id}_shipments_page_" . $page;

        return Cache::tags(['shipments_driver_' . $driver_id])->remember($cacheKey, 900, function () use ($driver_id) {
            return $this->shipmentRepository->get_shipments_for_driver($driver_id);
        });
    }

    public function get_shipments_with_insurance()
    {
        $page = request('page', 1);
        $cacheKey = "insured_shipments_page_" . $page;

        return Cache::tags(['shipments_insured'])->remember($cacheKey, 900, function () {
            return $this->shipmentRepository->get_shipments_with_insurance();
        });
    }

}
