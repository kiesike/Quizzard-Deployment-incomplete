<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizResultsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                $row['rank'] ?? '',
                $row['student_id'] ?? '',
                $row['surname'] ?? '',
                $row['first_name'] ?? '',
                $row['middle_initial'] ?? '',
                $row['gender'] ?? '',
                $row['grade_level'] ?? '',
                $row['section'] ?? '',
                $row['score'],
                $row['total_points'],
                $row['percentage'] . '%',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Rank',
            'Student ID',
            'Surname',
            'First Name',
            'M.I.',
            'Gender',
            'Grade Level',
            'Section',
            'Score',
            'Total',
            'Percentage',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}