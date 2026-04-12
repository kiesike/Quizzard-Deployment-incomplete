<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassDetailExport implements FromCollection, WithHeadings
{
    protected $students;
    protected $totalQuizzes;

    public function __construct($students, $totalQuizzes)
    {
        $this->students = $students;
        $this->totalQuizzes = $totalQuizzes;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'First Name',
            'Last Name',
            'Quizzes Taken',
            'Overall Grade (%)',
        ];
    }

    public function collection()
    {
        return $this->students->map(function ($student) {
            return [
                $student->studentProfile?->student_id ?? '—',
                $student->first_name,
                $student->surname,
                $student->quizzes_taken . ' / ' . $this->totalQuizzes,
                !is_null($student->overall_grade)
                    ? number_format($student->overall_grade, 2)
                    : 'N/A',
            ];
        });
    }
}