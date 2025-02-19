<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            DB::transaction(function () {
                $this->call(CourseSeeder::class);
                $this->call(UserSeeder::class);
                $this->call(LearningTokenSeeder::class);
                $this->call(EnrollmentSeeder::class);
                $this->call(ExamSeeder::class);
                $this->call(SubmissionSeeder::class);
            });
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
