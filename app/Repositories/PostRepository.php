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

    public function get_available_posts_for_vehicle($type, array $driverGovIds, $driverId)
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
            ->whereDoesntHave('drivers', function ($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            })
            ->with('governorates')
            ->latest()
            ->get();
        return $this->transform_posts($posts);
    }

    public function find_post_with_drivers(int $id)
    {
        return $this->post->with('drivers')->with('governorates')->find($id);
    }

    public function convert_post_to_shipment(Post $post, int $driverId, $price, $shipmentNumber, $pin, $qrPin, $deadline)
    {
        return DB::transaction(function () use ($post, $driverId, $price, $shipmentNumber, $pin, $qrPin, $deadline) {
            
            $shipment = Shipment::create([
                'user_id'            => $post->user_id,
                'driver_id'          => $driverId,
                'shipment_number'    => $shipmentNumber,
                'weight'             => $post->weight,
                'height'             => $post->height,
                'width'              => $post->width,
                'length'             => $post->length,
                'object'             => $post->object,
                'insurance'          => $post->insurance,
                'start_position_lat' => $post->start_position_lat,
                'start_position_lng' => $post->start_position_lng,
                'end_position_lat'   => $post->end_position_lat,
                'end_position_lng'   => $post->end_position_lng,
                'price'              => $price,
                'pin'                => $pin,
                'qr_pin'             => $qrPin,
                'status'             => 'جارية',
                'success'            => false,
                'delivery_deadline'  => $deadline,
            ]);

            $start = $post->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $post->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            $shipment->governorates()->attach([
                $start->id => ['start_end' => 'start']
            ]);
            $shipment->governorates()->attach([
                $end->id => ['start_end' => 'end']
            ]);

            $post->update([
                'finished' => true
            ]);

            return $shipment;
        });
    }

    public function get_applied_posts_by_driver_id($driverId)
    {
        $posts = $this->post->where('finished', false)->whereHas('drivers', function ($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        })
        ->with(['governorates', 'drivers' => function ($query) use ($driverId) {
            $query->where('driver_id', $driverId);
        }])
        ->latest()
        ->get();

        return $posts->map(function ($post) {

            $start = $post->governorates->where('pivot.start_end', 'start')->first();
            $post['start_governorate'] = $start?->name;

            $end = $post->governorates->where('pivot.start_end', 'end')->first();
            $post['end_governorate'] = $end?->name;

            $myOffer = $post->drivers->first();
            $post['my_price'] = $myOffer->pivot->price;
            $post['my_date']  = $myOffer->pivot->date;

            return $post->makeHidden(['governorates', 'drivers']);
        });
    }
    
}
