<?php

namespace App\Jobs;

use App\Models\ContractClause;
use App\Models\Enums\ReviewStatus;
use App\Models\Review;
use App\Services\Review\Manager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartReview implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Review $review
    ) {}

    public function handle(Manager $manager): void
    {
        if ($this->review->status !== ReviewStatus::PENDING && $this->review->status !== ReviewStatus::FAILED) {
            return;
        }

        $this->review->status = ReviewStatus::PROCESSING;
        $this->review->save();

        try {
            $output = $manager->driver('openai')->analyze($this->review->content);

            DB::transaction(function () use ($output) {
                $this->review->title = $output->getTitle();
                $this->review->summary = $output->getSummary();
                $this->review->risk_score = $output->getRiskScore();
                $this->review->risk_score_comment = $output->getRiskScoreComment();
                $this->review->status = ReviewStatus::COMPLETED;
                $this->review->save();

                foreach ($output->getClauses() as $clause) {
                    $contractClause = new ContractClause();
                    $contractClause->review_id = $this->review->id;
                    $contractClause->title = $clause->getTitle();
                    $contractClause->comment = $clause->getComment();
                    $contractClause->risk_score = $clause->getRiskScore();
                    $contractClause->risk_score_comment = $clause->getRiskScoreComment();
                    $contractClause->save();
                }
            });
        } catch (\Exception $e) {
            $this->review->status = ReviewStatus::FAILED;
            $this->review->save();

            Log::error($e->getMessage(), $e->getTrace());

            report($e);

            return;
        }
    }
}
