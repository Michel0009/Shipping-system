<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewFormRequest;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    protected ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function create_review(ReviewFormRequest $request)
    {
        $review = $this->reviewService->create_review($request->validated());

        return response()->json([
            'message' => 'تم إضافة التقييم بنجاح',
        ]);

    }

    public function get_driver_reviews()
    {
        $data = $this->reviewService->get_driver_reviews();

        return response()->json([
            'data' => $data
        ]);
    }
}
