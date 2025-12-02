<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

abstract class ReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $data;
    protected $sheetTitle;
    protected $headings;

    public function __construct($data, $title, $headings)
    {
        $this->data = $data;
        $this->sheetTitle = $title;
        $this->headings = $headings;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return (string) $this->sheetTitle;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function map($row): array
    {
        if (is_array($row)) {
            // Se for array associativo, retornar valores na ordem dos headings
            $result = [];
            foreach ($this->headings as $heading) {
                $result[] = $row[$heading] ?? '';
            }
            return $result;
        }
        return (array) $row;
    }
}

