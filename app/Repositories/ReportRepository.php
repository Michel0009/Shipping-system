<?php

namespace App\Repositories;

use App\Models\Report;
use App\Models\Warning;

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

    public function get_reports()
    {
        return $this->report->with(['reporter:id,first_name,last_name,phone_number,user_number',
            'reported_user:id,first_name,last_name,phone_number,user_number'])->latest()->get();
    }

    public function create_warning(array $data)
    {
        return Warning::create($data);
    }

}
