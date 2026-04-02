<?php

namespace App\Repositories;

use App\Models\Device_token;
use App\Models\Notification;

class NotificationRepository
{
    protected $notification;

    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    public function create(array $data)
    {
        return $this->notification->create($data);
    }

    public function update(array $data, $user_id)
    {
        return $this->notification->where('user_id', $user_id)->update($data);
    }

    public function get($user_id)
    {
        return $this->notification->where('user_id', $user_id)->latest()->get();
    }

    public function count($user_id)
    {
        return $this->notification->where('user_id', $user_id)->where('status', false)->count();
    }

    public function update_or_create_token(string $token, int $userId)
    {
        return Device_token::updateOrCreate(
            ['token' => $token],
            ['user_id' => $userId]
        );
    }

    public function delete_token(string $token)
    {
        return Device_token::where('token', $token)->delete();
    }
}