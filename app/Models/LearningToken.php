<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'owner_id',
        'student_id',
        'token',
        'description',
        'status',
        'redeemed_at',
        'expires_at',
        'deadline_at',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'learning_token_courses', 'token_id', 'course_id')
            ->using(LearningTokenCourse::class);
    }
}
