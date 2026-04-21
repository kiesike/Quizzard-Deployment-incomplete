<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;

class QuizResultsSheet implements FromArray, WithTitle
{
    protected $quizId;
    protected $request;

    public function __construct($quizId, Request $request)
    {
        $this->quizId = $quizId;
        $this->request = $request;
    }

    public function array(): array
    {
        $controller = new TeacherController();

        $response = $controller->quizResults($this->request, $this->quizId);
        $data = $response->getData(true);

        $rows = [];

        // Title
        $rows[] = ['QUIZ RESULTS'];
        $rows[] = [''];

        // Summary
        $rows[] = ['Total Attempts', $data['total_attempts']];
        $rows[] = ['Average Score', $data['average_score']];
        $rows[] = ['Average %', $data['average_percentage']];
        $rows[] = ['Passed', $data['pass_count']];
        $rows[] = ['Failed', $data['fail_count']];

        $rows[] = [''];

        // Header
        $rows[] = [
            'Student ID',
            'First Name',
            'Middle Initial',
            'Surname',
            'Email',
            'Score',
            'Total',
            'Percentage',
            'Status',
            'Completed At'
        ];

        foreach ($data['results'] as $r) {
            $rows[] = [
                $r['student_id'],
                $r['student_first_name'],
                $r['student_middle_initial'],
                $r['student_surname'],
                $r['student_email'],
                $r['score'],
                $r['total_points'],
                $r['percentage'],
                $r['is_passed'] ? 'PASSED' : 'FAILED',
                $r['completed_at'],
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Results';
    }
}