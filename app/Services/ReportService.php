<?php

namespace App\Services;

use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Support\Facades\Auth;


class ReportService
{

  protected $reportRepository;
  protected $userRepository;

  public function __construct(ReportRepository $reportRepository, UserRepository $userRepository)
  {
      $this->reportRepository = $reportRepository;
      $this->userRepository = $userRepository;
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

  public function get_reports()
  {
      return $this->reportRepository->get_reports();
  }

  public function send_warning(array $data)
  {
      $warningData = [
          'user_id' => $data['user_id'],
          'warning_text' => $data['warning_text'],
      ];

      $this->reportRepository->create_warning($warningData);
      app(\App\Services\NotificationService::class)->send_notification(
          $data['user_id'], $data['warning_text'], 0, 'تنبيه تحذيري', []
      );
  }

  public function get_user_warnings($user_id)
  {
      return $this->reportRepository->get_user_warnings($user_id);
  }

  public function send_notification_for_all(array $data)
  {
      $users = $this->userRepository->get_all_app_users();

      foreach ($users as $user) {
         app(\App\Services\NotificationService::class)->send_notification(
             $user->id, $data['notification_text'], 0, 'إعلان عام', []
         );
      }

  }

}
