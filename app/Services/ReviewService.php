<?php

namespace App\Services;

use App\Repositories\DriverRepository;
use App\Repositories\ReviewRepository;
use Illuminate\Support\Facades\Auth;


class ReviewService
{

  protected $reviewRepository;
  protected $driverRepository;

  public function __construct(ReviewRepository $reviewRepository, DriverRepository $driverRepository)
  {
      $this->reviewRepository = $reviewRepository;
      $this->driverRepository = $driverRepository;
  }

  public function create_review(array $data)
  {
      $user = Auth::user();

      $reviewData = [
          'user_id' => $user->id,
          'driver_id' => $data['driver_id'],
          'rate' => $data['rate'],
          'review' => $data['review'],
      ];

      return $this->reviewRepository->create($reviewData);
  }

  public function get_driver_reviews()
  {
      $user = Auth::user();
      $driver = $this->driverRepository->find_by_user_ID($user->id);
      $reviews = $this->reviewRepository->get_driver_reviews($driver->id);
      $average = $this->reviewRepository->get_driver_average_rate($driver->id);

      return [
          'average_rate' => round($average, 2),
          'reviews_count' => $reviews->count(),
          'reviews' => $reviews,
      ];
  }

}
