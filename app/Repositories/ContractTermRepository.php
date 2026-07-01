<?php

namespace App\Repositories;

use App\Models\Contract_term;

class ContractTermRepository
{
    protected $contractTerm;
    public function __construct(Contract_term $contractTerm)
    {
        $this->contractTerm = $contractTerm;
    }

    public function create(array $data)
    {
        return $this->contractTerm->create($data);
    }

    public function get_contract_terms()
    {
        return $this->contractTerm->orderBy('order')->get();
    }

    public function delete($id)
    {
        return $this->contractTerm->where('id', $id)->delete();
    }

    public function find($id)
    {
        return $this->contractTerm->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $term = $this->contractTerm->find($id);
        $term->update($data);
        return $term;
    }

    public function shift_orders_for_creation($newOrder)
    {
        $this->contractTerm->where('order', '>=', $newOrder)->increment('order');
    }

    public function shift_orders_for_update($oldOrder, $newOrder)
    {
        if ($newOrder < $oldOrder) {
            $this->contractTerm->where('order', '>=', $newOrder)
                               ->where('order', '<', $oldOrder)
                               ->increment('order');
        } elseif ($newOrder > $oldOrder) {
            $this->contractTerm->where('order', '>', $oldOrder)
                               ->where('order', '<=', $newOrder)
                               ->decrement('order');
        }
    }

    public function shift_orders_for_deletion($deletedOrder)
    {
        $this->contractTerm->where('order', '>', $deletedOrder)->decrement('order');
    }
}
