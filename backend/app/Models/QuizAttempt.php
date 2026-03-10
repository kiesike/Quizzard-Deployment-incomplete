<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'quiz_id',
        'score',
        'total_points',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Attempt belongs to a student
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Attempt belongs to a quiz
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}