<?php

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function created(Post $post)
    {
        Cache::forget('user_posts_' . $post->user_id);
    }

    public function updated(Post $post)
    {
        Cache::forget('user_posts_' . $post->user_id);
    }

    public function deleted(Post $post)
    {
        Cache::forget('user_posts_' . $post->user_id);
    }

}
