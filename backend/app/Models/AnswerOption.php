<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnswerOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'option_text',
        'is_correct',
        'match_pair',
        'order',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // Answer option belongs to a question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}