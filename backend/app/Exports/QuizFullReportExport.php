<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuizFullReportExport implements WithMultipleSheets
{
    protected $results;
protected $analytics;
protected $quizTitle;

public function __construct($results, $analytics, $quizTitle)
{
    $this->results = $results;
    $this->analytics = $analytics;
    $this->quizTitle = $quizTitle;
}

    public function sheets(): array
    {
        return [
            new QuizResultsExport($this->results, $this->quizTitle),
            new QuizAnalyticsExport($this->analytics, $this->quizTitle),
        ];
    }
}