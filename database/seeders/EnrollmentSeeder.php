<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\LearningToken;
use App\Models\LearningTokenCourse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $learningTokens = LearningToken::where('status', 'NOT_USED')->get();
            $students = User::where('email', '!=', 'system@dicoding.com')->pluck('id')->toArray();

            foreach ($learningTokens as $token) {
                // Randomly assign student
                do {
                    $studentId = $students[array_rand($students)];
                    $alreadyAssigned = LearningToken::where('student_id', $studentId)->exists();
                } while ($alreadyAssigned);

                $token->update([
                    'status' => 'USED',
                    'student_id' => $studentId,
                    'redeemed_at' => now()->addSeconds(rand(0, 3 * 24 * 60 * 60)),
                ]);

                $courseIds = LearningTokenCourse::where('token_id', $token->id)->pluck('course_id');

                foreach ($courseIds as $courseId) {
                    Enrollment::create([
                        'student_id' => $studentId,
                        'course_id' => $courseId,
                        'status' => 'ENROLLED',
                        'deadline_at' => $token->deadline_at,
                    ]);

                    Activity::create([
                        'resource_id' => $courseId,
                        'user_id' => $studentId,
                        'description' => 'COURSE_ENROLL',
                    ]);
                }

                Activity::create([
                    'resource_id' => $token->id,
                    'user_id' => $studentId,
                    'description' => 'TOKEN_REDEEM',
                ]);
            }
        });
    }
}
