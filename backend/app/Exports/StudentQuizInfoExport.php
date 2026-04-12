<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentQuizInfoExport implements FromCollection, WithHeadings
{
    protected $quizzes;

    public function __construct($quizzes)
    {
        $this->quizzes = $quizzes;
    }

    public function headings(): array
    {
        return [
            'Quiz Name',
            'Score',
            'Total',
            'Status',
            'Date Published',
            'Date Completed',
        ];
    }

    public function collection()
    {
        return $this->quizzes->map(function ($quiz) {
            return [
                $quiz->name,
                !is_null($quiz->score) ? number_format($quiz->score, 2) : '—',
                !is_null($quiz->total) ? number_format($quiz->total, 2) : '—',
                $quiz->status,
                $quiz->date_published?->format('M d, Y') ?? '—',
                $quiz->date_completed?->format('M d, Y h:i A') ?? '—',
            ];
        });
    }
}