<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{
    protected $review;
    public function __construct(Review $review)
    {
        $this->review = $review;
    }

    public function create(array $data): Review
    {
        return $this->review->updateOrCreate(
            [
                'user_id' => $data['user_id'],
                'driver_id' => $data['driver_id'],
            ],
            [
                'rate' => $data['rate'],
                'review' => $data['review'],
            ]
        );
    }

    public function get_driver_reviews($driverId)
    {
        return $this->review->with(['user:id,first_name,last_name,user_number'])
            ->where('driver_id', $driverId)->latest()->get();
    }

    public function get_driver_average_rate($driverId)
    {
        return $this->review->where('driver_id', $driverId)->avg('rate');
    }

}
