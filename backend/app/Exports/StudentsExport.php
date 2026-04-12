<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentsExport implements FromCollection, WithHeadings
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    public function headings(): array
    {
        return [
            'First Name',
            'Last Name',
            'Student ID',
            'Gender',
            'Date of Birth',
            'Contact',
            'Grade Level',
            'Section',
        ];
    }

    public function collection()
    {
        return collect($this->students);
    }
}