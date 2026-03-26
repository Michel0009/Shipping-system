<?php

namespace App\Services;

use App\Jobs\SendFcmNotification;
use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;

class NotificationService
{

    protected $notificationRepository;
    protected $userRepository;

    public function __construct(NotificationRepository $notificationRepository, UserRepository $userRepository)
    {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
    }

    public function send_notification(int $user_ID, string $message, int $complaint_number)
    {
        $notification = $this->notificationRepository->create([
          'user_id' => $user_ID,
          'message' => $message,
          'status' => false,
          'complaint_number' => $complaint_number
        ]);  

        $user = $this->userRepository->findByID($user_ID);
        $device_tokens = $user->device_token;

        foreach ($device_tokens as $device) {

            SendFcmNotification::dispatch($device->token, $notification);
        }

    }

    public function get_all_notifications()
    {
        $user = Auth::user();

        $notificatios = $this->notificationRepository->get($user->id);

        $this->notificationRepository->update(['status' => true], $user->id);
        
        return ['notifications' => $notificatios]; 
    }

    public function new_notifications_count()
    {
        $user = Auth::user();
        $notificatios = $this->notificationRepository->count($user->id);
        return $notificatios;
    }

    public function save_device_token(string $token)
    {
      $user = Auth::user();
      return $this->notificationRepository->update_or_create_token($token, $user->id);

    }


}