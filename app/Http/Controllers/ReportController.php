<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFormRequest;
use App\Services\ReportService;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function report(ReportFormRequest $request)
    {
        $this->reportService->create_report($request->validated());

        return response()->json([
            'message' => 'تم إرسال البلاغ بنجاح',
        ]);

    }

    public function get_reports()
    {
        $reports = $this->reportService->get_reports();
        return response()->json($reports);
    }

    public function send_warning(ReportFormRequest $request)
    {
        $this->reportService->send_warning($request->validated());

        return response()->json([
            'message' => 'تم إرسال التنبيه للمستخدم بنجاح',
        ]);
    }

    public function send_notification_for_all(ReportFormRequest $request)
    {
        $this->reportService->send_notification_for_all($request->validated());

        return response()->json([
            'message' => 'تم إرسال هذا الإشعار لجميع مستخدمين التطبيق بنجاح',
        ]);
    }
}
