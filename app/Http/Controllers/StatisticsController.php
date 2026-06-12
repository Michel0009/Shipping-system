<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticsFormRequest;
use App\Services\StatisticsService;

class StatisticsController extends Controller
{
    protected $statisticsService;
    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }
    public function get_statistics(StatisticsFormRequest $request)
    {
        $result = $this->statisticsService->get_statistics($request->validated());
        return response()->json($result);
    }
    public function get_general_statistics(){
        $result = $this->statisticsService->get_general_statistics();
        return response()->json($result);
    }
    public function export_statistics_pdf(StatisticsFormRequest $request){
        $result = $this->statisticsService->export_statistics_pdf($request->validated());
        return response($result)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="Statistics_Report.pdf"');
    }
}
