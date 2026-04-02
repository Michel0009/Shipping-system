<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ShipmentService
{

  // protected $userRepository;

  // public function __construct(UserRepository $userRepository)
  // {
  //     $this->userRepository = $userRepository;
  // }

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

}
