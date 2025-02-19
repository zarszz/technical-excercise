<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\LearningToken;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $learningTokens = LearningToken::with(['courses' => function ($query) {
            $query->where('can_graduate', true);
        }])
            ->where("status", "USED")->get();

        foreach ($learningTokens as $token) {
            foreach ($token->courses as $course) {
                $status = rand(1, 100) <= 5 ? 'WAITING_REVIEW' : 'APPROVED';
                $submittedAt = now()->addHours(rand(12, 23));

                $submission = Submission::create([
                    'course_id' => $course->id,
                    'student_id' => $token->student_id,
                    'filename' => fake()->uuid(),
                    'student_comment' => fake()->sentence(),
                    'status' => $status,
                    'created_at' => $submittedAt,
                    'updated_at' => $submittedAt,
                ]);

                if ($status === 'APPROVED') {
                    Activity::create([
                        'user_id' => $token->student_id,
                        'resource_id' => $submission->id,
                        'description' => 'SUBMISSION_APPROVED',
                        'created_at' => $submittedAt,
                        'updated_at' => $submittedAt,
                    ]);
                }
            }
        }
    }
}
