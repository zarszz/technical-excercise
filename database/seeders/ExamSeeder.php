<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Exam;
use App\Models\LearningToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $learningTokens = LearningToken::where("status", "USED")->get();

            foreach ($learningTokens as $token) {
                foreach ($token->courses as $course) {
                    $status = rand(1, 100) <= 5 ? 'IN_PROGRESS' : 'FINISHED';
                    $finishedAt = now()->addHours(rand(1, 23));

                    $exam = Exam::create([
                        'course_id' => $course->id,
                        'student_id' => $token->student_id,
                        'status' => $status,
                        'deadline_at' => now()->addDay(),
                        'finished_at' => $status === 'FINISHED' ? $finishedAt : null,
                    ]);

                    if ($status === 'FINISHED') {
                        Activity::create([
                            'user_id' => $token->student_id,
                            'resource_id' => $exam->id,
                            'description' => 'EXAM_FINISHED',
                            'created_at' => $finishedAt,
                            'updated_at' => $finishedAt,
                        ]);
                    }
                }
            }
        });
    }
}
