<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function get_all_notifications()
    {
        return response()->json($this->notificationService->get_all_notifications()); 
    }
    public function new_notifications_count()
    {
        return response()->json(['count' => $this->notificationService->new_notifications_count()]);
    }

    public function save_device_token(UserFormRequest $request)
    {
        $token = $request->validated()['token'];
        $this->notificationService->save_device_token($token);
        
        return response()->json(['message' => 'تم حفظ التوكين للجهاز']);
    }
}
