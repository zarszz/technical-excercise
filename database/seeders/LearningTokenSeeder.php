<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LearningToken;
use App\Models\LearningTokenCourse;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class LearningTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('email', 'system@dicoding.com')->delete();

        $systemUser = User::factory()->create([
            'name' => 'System',
            'email' => 'system@dicoding.com'
        ]);

        $courses = Course::all();

        $learningPaths = [
            ['name' => 'Laravel', 'courses' => ['Laravel Beginner', 'Laravel Intermediate', 'Laravel Expert', 'Intro to UI/UX', 'Intro to AWS']],
            ['name' => 'Android', 'courses' => ['Android Beginner', 'Android Intermediate', 'Android Expert', 'Intro to UI/UX', 'Intro to GenAI']],
            ['name' => 'React', 'courses' => ['React Beginner', 'React Intermediate', 'React Expert', 'Intro to UI/UX', 'Intro to GenAI']],
            ['name' => 'PHP', 'courses' => ['PHP Beginner', 'PHP Intermediate', 'PHP Expert', 'Intro to AWS', 'Intro to GenAI']],
            ['name' => 'Devops', 'courses' => ['Devops Beginner', 'Devops Intermediate', 'Devops Expert', 'Intro to AWS', 'Intro to Google Cloud']],
        ];

        foreach ($learningPaths as $learningPath) {
            for ($i = 0; $i < 20000; $i++) {
                $token = LearningToken::create([
                    'creator_id' => $systemUser->id,
                    'owner_id' => $systemUser->id,
                    'student_id' => null,
                    'token' => Str::random(10),
                    'description' => 'Learning token for ' . $learningPath['name'],
                    'status' => 'NOT_USED',
                    'redeemed_at' => null,
                    'expires_at' => now()->addDays(7),
                    'deadline_at' => now()->addDays(30),
                ]);

                foreach ($learningPath['courses'] as $course) {
                    LearningTokenCourse::create([
                        'token_id' => $token->id,
                        'course_id' => $courses->where('name', $course)->value('id'),
                    ]);
                }
            }
        }
    }
}
