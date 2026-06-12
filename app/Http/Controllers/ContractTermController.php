<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContractTermFormRequest;
use App\Services\ContractTermService;

class ContractTermController extends Controller
{
    protected ContractTermService $contractTermService;

    public function __construct(ContractTermService $contractTermService)
    {
        $this->contractTermService = $contractTermService;
    }

    public function create_contract_term(ContractTermFormRequest $request)
    {
        $this->contractTermService->create_contract_term($request->validated());

        return response()->json([
            'message' => 'تم إضافة بند جديد للعقد',
        ]);
    }

    public function get_contract_terms()
    {
        $contractTerms = $this->contractTermService->get_contract_terms();
        return response()->json($contractTerms);
    }

    public function delete_contract_term($id)
    {
        $this->contractTermService->delete_contract_term($id);

        return response()->json([
            'message' => 'تم حذف هذا البند من العقد',
        ]);
    }
    public function create_driver_contract(ContractTermFormRequest $request)
    {
        $result = $this->contractTermService->create_driver_contract($request->validated());
        return response($result)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="driver_contract.pdf"');
    }
}
