<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'is_published',
        'cover_image',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // A quiz belongs to a teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // A quiz has many questions
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // A quiz has many attempts
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }
}