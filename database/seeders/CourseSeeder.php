<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            ['name' => 'Laravel Beginner', 'can_graduate' => true],
            ['name' => 'Laravel Intermediate', 'can_graduate' => true],
            ['name' => 'Laravel Expert', 'can_graduate' => true],
            ['name' => 'Android Beginner', 'can_graduate' => true],
            ['name' => 'Android Intermediate', 'can_graduate' => true],
            ['name' => 'Android Expert', 'can_graduate' => true],
            ['name' => 'React Beginner', 'can_graduate' => true],
            ['name' => 'React Intermediate', 'can_graduate' => true],
            ['name' => 'React Expert', 'can_graduate' => true],
            ['name' => 'PHP Beginner', 'can_graduate' => true],
            ['name' => 'PHP Intermediate', 'can_graduate' => true],
            ['name' => 'PHP Expert', 'can_graduate' => true],
            ['name' => 'Devops Beginner', 'can_graduate' => true],
            ['name' => 'Devops Intermediate', 'can_graduate' => true],
            ['name' => 'Devops Expert', 'can_graduate' => true],
            ['name' => 'Intro to UI/UX', 'can_graduate' => false],
            ['name' => 'Intro to Rust', 'can_graduate' => false],
            ['name' => 'Intro to Machine Learning', 'can_graduate' => false],
            ['name' => 'Intro to GenAI', 'can_graduate' => false],
            ['name' => 'Intro to AWS', 'can_graduate' => false],
            ['name' => 'Intro to Google Cloud', 'can_graduate' => false],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
