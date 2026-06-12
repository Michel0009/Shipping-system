<?php

namespace App\Services;

use App\Repositories\ContractTermRepository;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

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
    public function create_driver_contract(array $data)
    {
        $terms = $this->contractTermRepository->get_contract_terms();
        $pdfData = [
            'first_party'  => (object) [
                'company_name'   => $data['company_name'],
                'cr_number'      => $data['cr_number'] ?? 'قيد الإصدار',
                'hq'             => $data['hq'],
                'representative' => $data['representative'],
            ],
            'second_party' => (object) [
                'name'             => $data['name'],
                'father_name'      => $data['father_name'],
                'mother_name'      => $data['mother_name'],
                'birth_place_date' => $data['birth_place_date'],
                'national_id'      => $data['national_id'],
                'amana'            => $data['amana'],
                'qaid'             => $data['qaid'],
                'address'          => $data['address'],
                'grant_date'       => $data['grant_date'],
            ],
            'terms' => $terms,
            'contract_date' => now()->format('Y-m-d')
        ];
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

        $htmlContent = view('reports.driver_contract_pdf', $pdfData)->render();

        $mpdf->WriteHTML($htmlContent);

        return $mpdf->Output('', 'S');
    }
}
