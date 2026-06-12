<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $driverId,
        public readonly float  $lat,
        public readonly float  $lng,
        public readonly string $updatedAt,
    ) {}

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
        return [
            'driver_id'  => $this->driverId,
            'lat'        => $this->lat,
            'lng'        => $this->lng,
            'updated_at' => $this->updatedAt,
        ];
    }
}
