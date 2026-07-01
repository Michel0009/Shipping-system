<?php

namespace App\Repositories;

use App\Models\Governorate;
use App\Models\Reward;
use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ShipmentRepository
{
    protected $shipment;
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function create(array $request, $shipmentNumber, $pin, $qrPin)
    {
        $shipmentData = $request['shipment'];

        $shipment = Shipment::create([
            'user_id' => $request['user_id'],
            'driver_id' => $request['driver_id'],
            'shipment_number' => $shipmentNumber,
            'weight' => $shipmentData['weight'],
            'height' => $shipmentData['height'],
            'width' => $shipmentData['width'],
            'length' => $shipmentData['length'],
            'object' => $shipmentData['object'],
            'start_position_lat' => $shipmentData['start_position_lat'],
            'start_position_lng' => $shipmentData['start_position_lng'],
            'end_position_lat' => $shipmentData['end_position_lat'],
            'end_position_lng' => $shipmentData['end_position_lng'],
            'price' => $request['price'],
            'pin' => $pin,
            'qr_pin' => $qrPin,
            'status' => 'جارية',
            'success' => false,
            'delivery_deadline' => now()->addDay(),
        ]);

        $shipment->governorates()->attach([
            $shipmentData['start_governorate_id'] => ['start_end' => 'start']
        ]);
        $shipment->governorates()->attach([
            $shipmentData['end_governorate_id'] => ['start_end' => 'end']
        ]);

        return $shipment;
    }

    public function find_shipment($id)
    {
        return $this->shipment->where('id', $id)->first();
    }

    public function find_shipment_by_id($id)
    {
        $shipment = $this->shipment->where('id', $id)->first();
        if ($shipment) {
            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            $shipment['start_governorate'] = $start?->name;
            $shipment['end_governorate'] = $end?->name;
        }
        return $shipment;
    }

    public function find_shipment_by_number($shipment_number)
    {
        $shipment = $this->shipment->where('shipment_number', $shipment_number)->first();
        if ($shipment) {
            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            $shipment['start_governorate'] = $start?->name;
            $shipment['end_governorate'] = $end?->name;
        }
        return $shipment;
    }

    public function save(Shipment $shipment): bool
    {
        return $shipment->save();
    }
    public function get_shipments_by_driver_id($driver_id)
    {
        return $this->shipment->where('driver_id', $driver_id)->get();
    }

    public function get_shipments_for_driver($driver_id)
    {
        $shipments = $this->shipment->where('driver_id', $driver_id)->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    public function get_shipments_for_user($user_id)
    {
        $shipments = $this->shipment->where('user_id', $user_id)->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    public function get_shipments()
    {
        $shipments = $this->shipment->with('governorates')->latest()->paginate(10);
        return $this->transform_shipments($shipments);
    }

    private function transform_shipments($shipments)
    {
        return $shipments->through(function ($shipment) {

            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            return [
                'id' => $shipment->id,
                'user_id' => $shipment->user_id,
                'driver_id' => $shipment->driver_id,
                'shipment_number' => $shipment->shipment_number,
                'width' => $shipment->width,
                'height' => $shipment->height,
                'length' => $shipment->length,
                'weight' => $shipment->weight,
                'object' => $shipment->object,
                'price' => $shipment->price,
                'status' => $shipment->status,
                'success' => $shipment->success,
                'start_governorate' => $start?->name,
                'end_governorate' => $end?->name,
            ];
        });
    }

    public function get_expired_shipments()
    {
        return $this->shipment->whereIn('status', ['قيد التوصيل', 'جارية'])
            ->where('delivery_deadline', '<', now())
            ->get();
    }

    public function get_active_shipments_for_user($user_id)
    {
        return $this->shipment->where('user_id', $user_id)
            ->whereIn('status', ['قيد التوصيل', 'جارية'])
            ->select('id', 'user_id', 'driver_id', 'shipment_number', 'price', 'status')->latest()->get();
    }

    public function get_active_shipments_for_driver($driver_id)
    {
        $shipments = $this->shipment->where('driver_id', $driver_id)
            ->select(
                'id',
                'user_id',
                'driver_id',
                'shipment_number',
                'price',
                'status',
                'start_position_lat',
                'start_position_lng',
                'end_position_lat',
                'end_position_lng'
            )
            ->whereIn('status', ['قيد التوصيل', 'جارية'])->latest()->get();

        foreach ($shipments as $shipment) {
            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();
            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            $shipment->start_governorate = $start?->name;
            $shipment->end_governorate = $end?->name;

            $shipment->makeHidden('governorates');
        }

        return $shipments;
    }

    public function get_driver_shipments_by_date($driver_id, $start_date, $end_date)
    {
        $shipments = $this->shipment->where('driver_id', $driver_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)->with('governorates')->latest()->get();
        return $this->get_shipments_without_paginate($shipments);
    }

    public function get_client_shipments_by_date($user_id, $start_date, $end_date)
    {
        $shipments = $this->shipment->where('user_id', $user_id)
            ->whereDate('created_at', '>=', $start_date)
            ->whereDate('created_at', '<=', $end_date)->with('governorates')->latest()->get();
        return $this->get_shipments_without_paginate($shipments);
    }

    private function get_shipments_without_paginate($shipments)
    {
        return $shipments->map(function ($shipment) {

            $start = $shipment->governorates
                ->where('pivot.start_end', 'start')
                ->first();

            $end = $shipment->governorates
                ->where('pivot.start_end', 'end')
                ->first();

            return [
                'id' => $shipment->id,
                'user_id' => $shipment->user_id,
                'driver_id' => $shipment->driver_id,
                'shipment_number' => $shipment->shipment_number,
                'width' => $shipment->width,
                'height' => $shipment->height,
                'length' => $shipment->length,
                'weight' => $shipment->weight,
                'object' => $shipment->object,
                'price' => $shipment->price,
                'status' => $shipment->status,
                'success' => $shipment->success,
                'start_governorate' => $start?->name,
                'end_governorate' => $end?->name,
            ];
        });
    }

    public function get_shipments_statisics_for_driver($driver_id)
    {
        $total = $this->shipment->where('driver_id', $driver_id)->count();
        $total_price = $this->shipment->where('driver_id', $driver_id)->sum('price');
        $unpaid_count = $this->shipment->where('driver_id', $driver_id)->where('paid', false)->count();
        $unpaid_amount = $this->shipment->where('driver_id', $driver_id)->where('paid', false)->sum('price');
        $all_bonuses = Reward::where('driver_id', $driver_id)->where('type', 'reward')->get();
        $unreceived_bonuses = $all_bonuses->where('received', false);
        $unreceived_bonuses_sum = $unreceived_bonuses->sum('value');
        $all_taxes = Reward::where('driver_id', $driver_id)->where('type', 'tax')->get();
        $unreceived_taxes = $all_taxes->where('received', false);
        $unreceived_taxes_sum = $unreceived_taxes->sum('value');

        return [
            'total' => $total,
            'total_price' => $total_price,
            'unpaid_count' => $unpaid_count,
            'unpaid_amount' => $unpaid_amount,
            'all_bonuses' => $all_bonuses,
            // 'unreceived_bonuses' => $unreceived_bonuses,
            'unreceived_bonuses_sum' => $unreceived_bonuses_sum,
            'all_taxes' => $all_taxes,
            // 'unreceived_taxes' => $unreceived_taxes,
            'unreceived_taxes_sum' => $unreceived_taxes_sum,
        ];
    }
    public function get_latest_active_shipment_by_user_id($user_id)
    {
        return $this->shipment->where('user_id', $user_id)
            ->whereIn('status', ['قيد التوصيل', 'جارية'])
            ->orderBy('delivery_deadline', 'desc')
            ->first();
    }

    public function get_latest_active_shipment_by_driver_id($driver_id)
    {
        return $this->shipment->where('driver_id', $driver_id)
            ->whereIn('status', ['قيد التوصيل', 'جارية'])
            ->orderBy('delivery_deadline', 'desc')
            ->first();
    }


    public function get_unpaid_counts($driver_id)
    {
        $unpaid_count = $this->shipment->where('driver_id', $driver_id)->where('paid', false)->count();
        $rewards = Reward::where('driver_id', $driver_id)->where('received', false)->count();

        return [
            'unpaid_count' => $unpaid_count,
            'rewards' => $rewards,
        ];
    }

    public function pay_shipments($driver_id)
    {
        $this->shipment->where('driver_id', $driver_id)->update(['paid' => true]);
        Reward::where('driver_id', $driver_id)->update(['received' => true]);
    }
    public function get_governorate_shipments_statistics(array $filters)
    {
        $startDate = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : Carbon::now()->startOfMonth();

        $endDate = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : Carbon::now()->endOfMonth();

        $query = Governorate::query();

        if (!empty($filters['governorate_ids'])) {
            $query->whereIn('id', $filters['governorate_ids']);
        }

        return $query->select('id', 'name')
            ->withCount(['shipments' => function ($shipmentQuery) use ($startDate, $endDate) {
                $shipmentQuery->whereBetween('shipments.created_at', [$startDate, $endDate])
                    ->where('governorate_shipment.start_end', 'start');
            }])->get();
    }
    public function get_earnings_statistics(array $filters)
    {
        $filterDate = $filters['filter_date'];

        $startDate = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])->startOfDay()
            : match ($filterDate) {
                'years'  => Carbon::now()->subYears(3)->startOfYear(),
                'months' => Carbon::now()->startOfYear(),
                'days'   => Carbon::now()->startOfMonth(),
            };
        $endDate = isset($filters['end_date'])
            ? Carbon::parse($filters['end_date'])->endOfDay()
            : match ($filterDate) {
                'years', 'months' => Carbon::now()->endOfYear(),
                'days'            => Carbon::now()->endOfDay(),
            };

        $isSameDay = $startDate->isSameDay($endDate);

        if ($isSameDay) {
            if ($filterDate !== 'days') {
                throw ValidationException::withMessages([
                    'filter_date' => 'يجب اختيار فلترة الأيام عندما يكون تاريخ البداية والنهاية في نفس اليوم لعرض تقسيم الساعات.'
                ]);
            }
        } else {
            if ($filterDate === 'months' && $startDate->isSameMonth($endDate)) {
                throw ValidationException::withMessages([
                    'filter_date' => 'لا يمكنك اختيار فلترة الأشهر لأن تاريخ البداية والنهاية يقعان ضمن نفس الشهر.'
                ]);
            }

            if ($filterDate === 'years' && $startDate->isSameYear($endDate)) {
                throw ValidationException::withMessages([
                    'filter_date' => 'لا يمكنك اختيار فلترة السنوات لأن تاريخ البداية والنهاية يقعان ضمن نفس السنة.'
                ]);
            }
        }

        $shipments = Shipment::whereBetween('created_at', [$startDate, $endDate])
            ->get(['price', 'paid', 'created_at']);

        $unpaidShipments = $shipments->where('paid', false);

        $unpaidShipmentsCount = $unpaidShipments->count();
        $unpaidShipmentsEarnings = (float) ($unpaidShipments->sum('price') * 0.15);

        $groupedShipments = $shipments->groupBy(function ($shipment) use ($filterDate, $isSameDay) {
            if ($isSameDay) {
                return $shipment->created_at->format('Y-m-d H:00');
            }

            if ($filterDate === 'years') {
                return $shipment->created_at->format('Y');
            } elseif ($filterDate === 'months') {
                return $shipment->created_at->format('Y-m');
            } else {
                return $shipment->created_at->format('Y-m-d');
            }
        });

        $earningsByDate = $groupedShipments->map(function ($group) {
            return (float) ($group->sum('price') * 0.15);
        });

        return [
            'unpaid_shipments_count'    => $unpaidShipmentsCount,
            'unpaid_shipments_earnings' => $unpaidShipmentsEarnings,
            'earnings_by_date'          => $earningsByDate,
        ];
    }
    public function get_shipments_count_today()
    {
        return $this->shipment->where('created_at', '>=', now()->startOfDay())->count();
    }
    public function get_this_month_earnings()
    {
        return (float) ($this->shipment->where('created_at', '>=', now()->startOfMonth())->sum('price') * 0.15);
    }
}
