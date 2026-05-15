<?php

namespace App\Services;

use App\Repositories\ContractTermRepository;

class ContractTermService
{

  protected $contractTermRepository;

  public function __construct(ContractTermRepository $contractTermRepository)
  {
      $this->contractTermRepository = $contractTermRepository;
  }

  public function create_contract_term(array $data)
  {
      $this->contractTermRepository->create($data);
  }

  public function get_contract_terms()
  {
      return $this->contractTermRepository->get_contract_terms();
  }

  public function delete_contract_term($id)
  {
      return $this->contractTermRepository->delete($id);
  }


}
