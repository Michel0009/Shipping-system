<?php

namespace App\Services;

use App\Repositories\DriverRepository;
use App\Repositories\ShipmentRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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
          throw new \Exception('لديك طلب شحنة قيد المعالجة بالفعل');
      }

      return $payload;
  }

  public function get_shipment_request()
  {
      $user = Auth::user();

      $cacheKey = "shipment_request_user_" . $user->id;
      $shipment = Cache::get($cacheKey);

      if (!$shipment) {
          throw new \Exception('لا يوجد طلب شحنة حالياً');
      }
      return $shipment;
  }

  public function delete_shipment_request()
  {
      $user = Auth::user();

      $cacheKey = "shipment_request_user_" . $user->id;

      if (!Cache::has($cacheKey)) {
          throw new \Exception('لا يوجد طلب شحنة لحذفه');
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
          throw new \Exception('لا يوجد طلب شحنة لتعديله');
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
          throw new \Exception('لا يوجد طلب شحنة');
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
          throw new Exception('لا يوجد طلب شحنة');
      }
      $driver = $this->driverRepository->find_driver($data['driver_id']);

      if (!$driver->availability) {
          return "هذا السائق غير متاح حاليا";
      }

      $startGov = $this->driverRepository->find_governorate($shipment['start_governorate_id']);
      $endGov = $this->driverRepository->find_governorate($shipment['end_governorate_id']);
      $shipment['start_governorate'] = $startGov->name;
      $shipment['end_governorate'] = $endGov->name;

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
          return "تم إرسال طلب لهذا السائق مسبقاً";
      }

      app(\App\Services\NotificationService::class)->send_notification($driver->user_id,
          'لديك طلب شحنة جديد. الرجاء التحقق منه والتعامل معه', 0, 'طلب شحنة جديد', $payload
      );

      return "تم إرسال الطلب إلى السائق";
  }


  public function respond_to_request(array $data)
  {
      $user = Auth::user();
      $driver =  $this->driverRepository->find_by_user_ID($user->id);

      $requestKey = "driver_request_{$driver->id}_user_{$data['user_id']}";
      $request = Cache::get($requestKey);

      if (!$request) {
          throw new Exception('انتهت صلاحية الطلب أو أنه غير موجود');
      }

      if (!$data['action']) {

          Cache::forget($requestKey);

          app(\App\Services\NotificationService::class)->send_notification($data['user_id'],
              'تم رفض طلب الشحنة من قبل السائق', 0, 'رفض الطلب', []
          );

          return "تم رفض الطلب";
      }
      $shipmentNumber = time() . rand(100, 999);
      $pin = random_int(100000, 999999);

      $shipment = $this->shipmentRepository->create(
          $request,
          $shipmentNumber,
          $pin
      );

      Cache::forget($requestKey);
      Cache::forget("shipment_request_user_" . $data['user_id']);

      app(\App\Services\NotificationService::class)->send_notification($data['user_id'],
          "تم قبول طلب الشحنة بنجاح. يمكنك متابعتها الآن باستخدام رقم الشحنة {$shipmentNumber} المولّد.",
           $shipment->id, 'قبول الطلب', $shipment
      );

      return "تم قبول الطلب وإنشاء الشحنة";
  }

}
