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
}
