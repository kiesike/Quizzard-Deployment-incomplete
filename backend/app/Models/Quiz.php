<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    // ✅ ADD THIS RELATIONSHIP
    public function classes()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'class_quizzes',
            'quiz_id',
            'class_id'
        );
    }
}