<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ProductExport implements WithEvents
{   
    use Exportable, RegistersEventListeners;
    protected $columnDepedencies;
    protected $columnExtraAttribute;
 
    function __construct($columnDepedencies,$columnExtraAttribute) {
        $this->columnDepedencies = $columnDepedencies;
        $this->columnExtraAttribute = $columnExtraAttribute;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $headerTitle = array(
                    'font' => array(
                        'name'      =>  'Calibri',
                        'size'      =>  18,
                        'bold'      =>  true
                    )
                );

                $styleBorder = [
                    'font' => [
                        'bold' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
    
                    ]
                ];

                $styleBorder2 = [
                    'font' => [
                        'bold' => true,
                    ]
                ];

                $mergeCells = [];
                //set field header
                $justColumn = [
                    'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P',
                    'Q','R','S','T','U','V','W','X','Y','Z'
                ];
                $allColumns = [
                    'A1','B1','C1','D1','E1','F1','G1','H1','I1','J1','K1','L1','M1','N1','O1','P1',
                    'Q1','R1','S1','T1','U1','V1','W1','X1','Y1','Z1'
                ];
                $allColumns2 = [
                    'A2','B2','C2','D2','E2','F2','G2','H2','I2','J2','K2','L2','M2','N2','O2','P2',
                    'Q2','R2','S2','T2','U2','V2','W2','X2','Y2','Z2'
                ];
                $end = null;
                foreach ($this->columnDepedencies as $key => $value) {
                    $event->sheet->setCellValue($allColumns[$key], $value);
                    $end = $key;
                }
                foreach ($this->columnDepedencies as $key => $value) {
                    $no = 2;
                    if ($this->columnExtraAttribute[$key]) {
                        if (gettype($this->columnExtraAttribute[$key]) != 'array') {
                            $event->sheet->setCellValue($allColumns2[$key], $this->columnExtraAttribute[$key]);
                        }else {
                            // set dropdown column
                            $drop_column = $justColumn[$key];
                            // set dropdown options
                            $options = [];
                            foreach ($this->columnExtraAttribute[$key] as $key2 => $attr) {
                                array_push($options,$attr);
                            }
                            // set dropdown list for first data row
                            $validation = $event->sheet->getCell("{$drop_column}{$no}")->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST );
                            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION );
                            $validation->setAllowBlank(false);
                            $validation->setShowInputMessage(true);
                            $validation->setShowErrorMessage(true);
                            $validation->setShowDropDown(true);
                            $validation->setErrorTitle('Input error');
                            $validation->setError('Value is not in list.');
                            $validation->setPromptTitle('Choose from list');
                            $validation->setPrompt('Please choose a value from the drop-down list.');
                            $validation->setFormula1(sprintf('"%s"',implode(',',$options)));

                            // clone
                            for ($x = 3; $x <= 100; $x++) {
                                $event->sheet->getCell("{$drop_column}{$x}")->setDataValidation(clone $validation);
                            }

                            // set columns to autosize
                            for ($y = 1; $y <= 100; $y++) {
                                $column = Coordinate::stringFromColumnIndex($y);
                                $event->sheet->getColumnDimension($column)->setAutoSize(true);
                            }
                        }
                    }
                    $end = $key;
                }
            }
        ];
    }
}
