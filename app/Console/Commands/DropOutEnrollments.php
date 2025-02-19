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
    private function dropOutEnrollmentsBefore(Carbon $deadline)
    {
        $enrollmentsToBeDroppedOut = Enrollment::where('deadline_at', '<=', $deadline)->get();

        $this->info('Enrollments to be dropped out: ' . count($enrollmentsToBeDroppedOut));
        $droppedOutEnrollments = 0;

        foreach ($enrollmentsToBeDroppedOut as $enrollment) {
            $hasActiveExam = Exam::where('course_id', $enrollment->course_id)
                ->where('student_id', $enrollment->student_id)
                ->where('status', 'IN_PROGRESS')
                ->exists();

            $hasWaitingReviewSubmission = Submission::where('course_id', $enrollment->course_id)
                ->where('student_id', $enrollment->student_id)
                ->where('status', 'WAITING_REVIEW')
                ->exists();

            if ($hasActiveExam || $hasWaitingReviewSubmission) {
                continue;
            }

            $enrollment->update([
                'status' => 'DROPOUT',
                'updated_at' => now(),
            ]);

            Activity::create([
                'resource_id' => $enrollment->id,
                'user_id' => $enrollment->student_id,
                'description' => 'COURSE_DROPOUT',
            ]);

            $droppedOutEnrollments++;
        }

        $this->info('Excluded from drop out: ' . count($enrollmentsToBeDroppedOut) - $droppedOutEnrollments);
        $this->info('Final dropped out enrollments: ' . $droppedOutEnrollments);
    }
}
