<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuizResultsExport implements FromCollection, ShouldAutoSize, WithStyles, WithTitle
{
    protected $rows;
    protected $quizTitle;

    public function __construct(Collection $rows, $quizTitle)
    {
        $this->rows = $rows;
        $this->quizTitle = $quizTitle;
    }

    public function title(): string
    {
        return 'Results';
    }

    public function collection()
    {
        $data = $this->rows->map(function ($row) {
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

        return collect([
            ['QUIZ RESULTS REPORT'],
            [$this->quizTitle],
            [''],
            [
                'Rank', 'Student ID', 'Surname', 'First Name', 'M.I.',
                'Gender', 'Grade Level', 'Section', 'Score', 'Total', 'Percentage',
            ],
        ])->merge($data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => 'center'],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 12],
                'alignment' => ['horizontal' => 'center'],
            ],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
