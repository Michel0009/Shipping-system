<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use Exception;
use Illuminate\Support\Facades\Auth;


class ReportService
{

  protected $reportRepository;

  public function __construct(ReportRepository $reportRepository)
  {
      $this->reportRepository = $reportRepository;
  }

  public function create_report(array $data)
  {
      $user = Auth::user();

      if ($user->id == $data['reported_id']) {
          throw new Exception('لا يمكنك الإبلاغ عن نفسك');
      }

      $reportData = [
          'reporter_id' => $user->id,
          'reported_id' => $data['reported_id'],
          'type' => $data['type'],
          'description' => $data['description'],
      ];

      $this->reportRepository->create($reportData);
  }

}
