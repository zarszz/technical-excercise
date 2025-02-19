<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class LearningTokenCourse extends Pivot
{
    public $incrementing = true;

    protected $table = "learning_token_courses";
    protected $fillable = ["token_id", "course_id"];

    public function token()
    {
        return $this->belongsTo(LearningToken::class, "token_id");
    }

    public function course()
    {
        return $this->belongsTo(Course::class, "course_id");
    }
}
