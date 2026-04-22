<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizAnalyticsExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
{
    protected $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function title(): string
    {
        return 'Analytics';
    }

    public function collection()
    {
        return $this->rows->map(function ($row) {
            return [
                $row['order'] ?? '-',
                $row['question_text'] ?? '-',
                $row['question_type'] ?? '-',
                $row['points'] ?? 0,
                $row['attempted_count'] ?? 0,
                $row['correct_count'] ?? 0,
                ($row['correct_rate'] ?? 0) . '%',
                $row['average_points'] ?? 0,
                $row['difficulty'] ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Question',
            'Type',
            'Points',
            'Attempted',
            'Correct Count',
            'Correct %',
            'Average Points',
            'Difficulty',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
