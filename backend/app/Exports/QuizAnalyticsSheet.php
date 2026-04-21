<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Http\Controllers\TeacherController;
use Illuminate\Http\Request;

class QuizAnalyticsSheet implements FromArray, WithTitle
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

        $response = $controller->quizAnalytics($this->request, $this->quizId);
        $data = $response->getData(true);

        $rows = [];

        // Title
        $rows[] = ['QUIZ ANALYTICS'];
        $rows[] = [''];

        // Summary
        $summary = $data['summary'];

        $rows[] = ['SUMMARY'];
        $rows[] = ['Average Score', $summary['average_score']];
        $rows[] = ['Highest Score', $summary['highest_score']];
        $rows[] = ['Lowest Score', $summary['lowest_score']];
        $rows[] = ['Attempts', $summary['attempt_count']];
        $rows[] = ['Pass Rate', $summary['pass_rate']];
        $rows[] = ['Std Dev', $summary['standard_deviation']];

        $rows[] = [''];

        // Difficulty Table
        $rows[] = ['QUESTION ANALYSIS'];
        $rows[] = ['Question', 'Text', 'Correct %', 'Difficulty'];

        foreach ($data['difficulty_analysis'] as $q) {
            $rows[] = [
                $q['question_label'],
                $q['question_text'],
                $q['correct_rate'] . '%',
                $q['difficulty'],
            ];
        }

        $rows[] = [''];

        // Comparison Table
        $rows[] = ['QUIZ COMPARISON'];
        $rows[] = ['Quiz', 'Avg Score', 'Attempts', 'Pass Rate'];

        foreach ($data['quiz_comparison'] as $c) {
            $rows[] = [
                $c['quiz_title'],
                $c['average_score'],
                $c['attempt_count'],
                $c['pass_rate'] . '%',
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Analytics';
    }
}