<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'media_path',
        'media_type',
        'points',
        'order',
    ];

    // Question belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // Question has many answer options
    public function answerOptions()
    {
        return $this->hasMany(AnswerOption::class);
    }

    // Question has many student answers
    public function studentAnswers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
