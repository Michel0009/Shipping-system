<?php

namespace App\Repositories;

use App\Models\Post;

class PostRepository
{
    protected $post;
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    public function create(array $data)
    {
        $post = $this->post->create($data);
        $post->governorates()->attach([
            $data['start_governorate_id'] => ['start_end' => 'start']
        ]);
        $post->governorates()->attach([
            $data['end_governorate_id'] => ['start_end' => 'end']
        ]);
        return $post;
    }

    public function find($id)
    {
        return $this->post->findOrFail($id);
    }

    public function update(Post $post, array $data)
    {
        $post->update($data);
        return $post;
    }

    public function delete(Post $post)
    {
        return $post->delete();
    }

    public function get_user_posts($userId)
    {
        $posts = $this->post->where('user_id', $userId)->with('governorates')->latest()->get();
        return $this->transform_posts($posts);
    }

    private function transform_posts($posts)
    {
        return $posts->map(function ($post) {

            $start = $post->governorates
                ->where('pivot.start_end', 'start')
                ->first();
            $post['start_governorate'] = $start?->name;

            $end = $post->governorates
                ->where('pivot.start_end', 'end')
                ->first();
            $post['end_governorate'] = $end?->name;

            return $post->makeHidden(['governorates']);
        });
    }
    
}
