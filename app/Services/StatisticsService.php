<?php


namespace App\Services;

use App\Repositories\ShipmentRepository;
use App\Repositories\UserRepository;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
class StatisticsService
{
    protected $userRepository;
    protected $shipmentRepository;
    public function __construct(UserRepository $userRepository, ShipmentRepository $shipmentRepository)
    {
        $this->userRepository = $userRepository;
        $this->shipmentRepository = $shipmentRepository;
    }
    public function get_statistics(array $data)
    {
        $shipmentsCount = $this->shipmentRepository->get_governorate_shipments_statistics($data);
        $shipmintsEarnings= $this->shipmentRepository->get_earnings_statistics($data);
        return [
            'shipments_count_statistics'=>$shipmentsCount,
            'shipments_earnings_statistics'=>$shipmintsEarnings,
        ];
    }
    public function get_general_statistics()
    {
        $shipmentsCount = $this->shipmentRepository->get_shipments_count_today();
        $earnings = $this->shipmentRepository->get_this_month_earnings();
        $clientsCount = $this->userRepository->get_clients_count();
        $driversCount = $this->userRepository->get_drivers_count();
        $userStatistics = $this->userRepository->get_user_statistics();
        return [
            'shipments_count_today' => $shipmentsCount,
            'this_month_earnings' => $earnings,
            'clients_count' => $clientsCount,
            'drivers_count' => $driversCount,
            'user_statistics' => $userStatistics,
        ];
    }
    public function get_report_data(array $data)
    {
        return [
            'today_shipments'        => $this->shipmentRepository->get_shipments_count_today(),
            'this_month_earnings'    => $this->shipmentRepository->get_this_month_earnings(),
            'total_clients'          => $this->userRepository->get_clients_count(),
            'total_drivers'          => $this->userRepository->get_drivers_count(),
            'governorate_stats'      => $this->shipmentRepository->get_governorate_shipments_statistics($data),
            'earnings_stats'         => $this->shipmentRepository->get_earnings_statistics($data),
            'user_stats'             => $this->userRepository->get_user_statistics(),
        ];
    }
    public function export_statistics_pdf(array $filters): string
    {
        $reportData = $this->get_report_data($filters);

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $defaultFontConfig = (new FontVariables())->getDefaults();

        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 15,
            'margin_bottom' => 15,
            'fontDir'       => array_merge($defaultConfig['fontDir'], [resource_path('fonts')]),
            'fontData'      => $defaultFontConfig + [
                'cairo' => [
                    'R'          => 'Cairo-Regular.ttf',
                    'B'          => 'Cairo-Bold.ttf',
                    'useOTL'     => 0xFF,
                    'useKashida' => 75,
                ]
            ],
            'default_font'  => 'cairo'
        ]);

        $htmlContent = view('reports.statistics_pdf', $reportData)->render();

        $mpdf->WriteHTML($htmlContent);

        return $mpdf->Output('', 'S');
    }
}
