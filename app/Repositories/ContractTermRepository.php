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
}
