<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $driverId,
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $phone_number,
        public readonly float  $lat,
        public readonly float  $lng,
        public readonly string $updatedAt,
    ) {
        Log::channel('daily')->info('DriverLocationUpdated event created', [
            'driver_id'  => $this->driverId,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone_number' => $this->phone_number,
            'lat'        => $this->lat,
            'lng'        => $this->lng,
            'updated_at' => $this->updatedAt,
            'created_at' => now()->toISOString(),
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-tracking'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        $payload = [
            'driver_id'  => $this->driverId,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'phone_number' => $this->phone_number,
            'lat'        => $this->lat,
            'lng'        => $this->lng,
            'updated_at' => $this->updatedAt,
        ];

        Log::channel('daily')->info('DriverLocationUpdated broadcasting payload', [
            'channel'      => 'private-admin-tracking',
            'event_name'   => $this->broadcastAs(),
            'payload'      => $payload,
            'broadcast_at' => now()->toISOString(),
        ]);

        return $payload;
    }
}
