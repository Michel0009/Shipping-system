<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    
    public function attach_driver(Post $post, $driverId, array $pivotData)
    {
        return $post->drivers()->syncWithoutDetaching([
            $driverId => $pivotData
        ]);
    }

    public function detach_driver(Post $post, $driverId)
    {
        return $post->drivers()->detach($driverId);
    }

    public function get_post_details($id)
    {
        return $this->post->where('id', $id)
            ->with([
                'governorates',
                'drivers.user',
                'drivers.car.vehicle_type',
                'drivers.reviews',
                'drivers.badge'
            ])->firstOrFail();
    }

    public function get_available_posts_for_vehicle($type, array $driverGovIds)
    {
        $posts = $this->post->where('finished', false)
            ->where('weight', '>=', $type->min_weight)->where('weight', '<=', $type->max_weight)
            ->where('length', '>=', $type->min_length)->where('length', '<=', $type->max_length)
            ->where('width', '>=', $type->min_width)->where('width', '<=', $type->max_width)
            ->where('height', '>=', $type->min_height)->where('height', '<=', $type->max_height)
            ->whereHas('governorates', function ($query) use ($driverGovIds) {
                $query->whereIn('governorate_id', $driverGovIds)
                      ->where('governorate_post.start_end', 'start');
            })
            ->whereHas('governorates', function ($query) use ($driverGovIds) {
                $query->whereIn('governorate_id', $driverGovIds)
                      ->where('governorate_post.start_end', 'end');
            })
            ->with('governorates')
            ->latest()
            ->get();
        return $this->transform_posts($posts);
    }

    // public function find_post_for_assignment(int $postId, int $userId)
    // {
    //     return Post::with('drivers')
    //         ->where('id', $postId)
    //         ->where('user_id', $userId)
    //         ->first();
    // }

    // public function convertPostToShipment(Post $post, int $driverId, float $price, $deliveryDeadline)
    // {
    //     return DB::transaction(function () use ($post, $driverId, $price, $deliveryDeadline) {

    //         $shipment = Shipment::create([
    //             'user_id' => $post->user_id,
    //             'driver_id' => $driverId,
    //             'weight' => $post->weight,
    //             'length' => $post->length,
    //             'width' => $post->width,
    //             'height' => $post->height,
    //             'price' => $price,
    //             'delivery_deadline' => $deliveryDeadline,
    //             'status' => 'ongoing',
    //             'pin'               => random_int(100000, 999999),
    //             'qr_code'           => Str::uuid()->toString(),
    //             'start_lat'         => $post->start_lat,
    //             'start_lng'         => $post->start_lng,
    //             'end_lat'           => $post->end_lat,
    //             'end_lng'           => $post->end_lng,
    //         ]);

    //         // 2. ربط المحافظات بالشحنة الجديدة (علاقة Many-to-Many للبداية والنهاية)
    //         // نأخذ المحافظات المرتبطة بالبوست الأساسي وننقلها للشحنة
    //         if ($post->start_governorate_id && $post->end_governorate_id) {
    //             $shipment->governorates()->attach([
    //                 $post->start_governorate_id => ['type' => 'start'],
    //                 $post->end_governorate_id   => ['type' => 'end']
    //             ]);
    //         }

    //         // 3. تحديث حالة المنشور إلى منتهي (finished = true) حتى لا يظهر بالاعلانات
    //         $post->update([
    //             'finished' => true
    //         ]);

    //         return $shipment;
    //     });
    // }
    
}
