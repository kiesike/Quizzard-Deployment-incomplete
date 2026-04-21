<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassQuizDetailExport implements FromCollection, WithHeadings
{
    protected $students;
    protected $totalPoints;

    public function __construct($students, $totalPoints)
    {
        $this->students = $students;
        $this->totalPoints = $totalPoints;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'First Name',
            'Last Name',
            'Score',
            'Percentage',
            'Status',
        ];
    }

    public function collection()
    {
        return $this->students->map(function ($student) {
            return [
                $student->studentProfile?->student_id ?? 'N/A',
                $student->first_name,
                $student->surname,
                !is_null($student->quiz_score) ? $student->quiz_score : 'N/A',
                !is_null($student->quiz_percentage) ? number_format($student->quiz_percentage, 2) . '%' : 'N/A',
                $student->quiz_status,
            ];
        });
    }
}