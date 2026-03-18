<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFormRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function report(ReportFormRequest $request)
    {
        $report = $this->reportService->create_report($request->validated());

        return response()->json([
            'message' => 'تم إرسال البلاغ بنجاح',
        ]);

    }
}
