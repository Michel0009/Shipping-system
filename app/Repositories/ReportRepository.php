<?php

namespace App\Repositories;

use App\Models\Report;

class ReportRepository
{
    protected $report;
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function create(array $data): Report
    {
        return $this->report->create($data);
    }

}
