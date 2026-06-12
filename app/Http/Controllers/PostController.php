<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostFormRequest;
use App\Services\PostService;

class PostController extends Controller
{

    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function create_post(PostFormRequest $request)
    {
        $post = $this->postService->create_post($request->validated());
        return response()->json([
            'message' => 'تم نشر إعلان الشحنة وحساب الأسعار التقديرية بنجاح.',
            'data' => $post
        ], 200);
    }

    public function update_prices(PostFormRequest $request)
    {
        $post = $this->postService->update_prices($request->validated());
        return response()->json([
            'message' => 'تم تثبيت نطاق الأسعار بنجاح.',
            'data' => $post
        ]);
    }

    public function delete_post($id)
    {
        $this->postService->delete_post($id);
        return response()->json([
            'message' => 'تم حذف الإعلان بنجاح.'
        ]);
    }

    public function get_my_posts()
    {
        $posts = $this->postService->get_my_posts();
        return response()->json($posts);
    }

    public function apply_post(PostFormRequest $request)
    {
        $result = $this->postService->apply_to_post($request->validated());
        return response()->json([
            'message' => $result['message']
        ], $result['code']);
    }

    public function cancel_apply($id)
    {
        $result = $this->postService->cancel_application($id);
        return response()->json([
            'message' => $result['message']
        ], $result['code']);
    }

    public function get_post_details($id)
    {
        $result = $this->postService->get_post_details($id);
        return response()->json($result);
    }

    public function suitable_posts_for_driver()
    {
        $posts = $this->postService->get_suitable_posts_for_driver();

        return response()->json($posts);
    }

    public function choose_driver_for_post(PostFormRequest $request)
    {
        $shipment = $this->postService->choose_driver_for_post($request->validated());

        return response()->json([
            'message' => 'تم قبول عرض السائق بنجاح، وتحويل الإعلان إلى شحنة جارية، وإغلاق الإعلان.',
            'data'    => $shipment
        ], 200);
    }

    public function get_applied_posts()
    {
        $posts = $this->postService->get_applied_posts();
        return response()->json($posts);
    }
}
