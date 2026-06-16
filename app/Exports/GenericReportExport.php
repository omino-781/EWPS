<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GenericReportExport implements FromArray, WithHeadings
{
    public function __construct(protected array $rows) {}

    public function array(): array
    {
        return array_map(fn ($row) => array_values($row), $this->rows);
    }

    public function headings(): array
    {
        if (empty($this->rows)) {
            return [];
        }

        return array_keys($this->rows[0]);
    }
}
