<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Submission;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Stopwatch\Stopwatch;

class DropOutEnrollments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enrollments:dropout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dropout enrollments on specified date.';

    public function __construct(
        private readonly Stopwatch $stopwatch,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $deadline = Carbon::parse(Enrollment::latest('id')->value('deadline_at'));

            $this->stopwatch->start(__CLASS__);

            $this->dropOutEnrollmentsBefore($deadline);

            $this->stopwatch->stop(__CLASS__);
            $this->info($this->stopwatch->getEvent(__CLASS__));

            DB::rollBack();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * The dropout process should fulfil the following requirements:
     * 1. The enrollment deadline has passed.
     * 2. The student has no active exam.
     * 3. The student has no submission waiting for review.
     * 4. Update the enrollment status to `DROPOUT`.
     * 5. Create an activity log for the student.
     */
    private function dropOutEnrollmentsBefore(Carbon $deadline): void
    {
        // Fetch enrollments that are eligible for dropout
        $enrollments = Enrollment::where('deadline_at', '<=', $deadline)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('exams')
                    ->whereColumn('exams.course_id', 'enrollments.course_id')
                    ->whereColumn('exams.student_id', 'enrollments.student_id')
                    ->where('exams.status', 'IN_PROGRESS');
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('submissions')
                    ->whereColumn('submissions.course_id', 'enrollments.course_id')
                    ->whereColumn('submissions.student_id', 'enrollments.student_id')
                    ->where('submissions.status', 'WAITING_REVIEW');
            })
            ->select('id', 'student_id')
            ->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        // Bulk update enrollments to 'DROPOUT' status
        $enrollments->chunk(1000)->each(function ($chunk) {
            Enrollment::whereIn('id', $chunk->pluck('id'))
                ->update(['status' => 'DROPOUT', 'updated_at' => now()]);
        });


        // Bulk insert activity logs
        $activities = $enrollments->map(fn($enrollment) => [
            'resource_id' => $enrollment->id,
            'user_id' => $enrollment->student_id,
            'description' => 'COURSE_DROPOUT',
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        collect($activities)->chunk(1000)->each(function ($chunk) {
            Activity::insert($chunk->toArray());
        });

        $this->info('Final dropped out enrollments: ' . $enrollments->count());
    }
}
