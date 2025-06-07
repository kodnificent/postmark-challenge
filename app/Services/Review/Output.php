<?php

namespace App\Services\Review;

class Output
{
    public function __construct(
        protected array $data
    ) {}

    public function toArray(): array
    {
        return $this->data;
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getSummary(): string
    {
        return $this->data['summary'];
    }

    /**
     * @return Clause[]
     */
    public function getClauses(): array
    {
        return collect($this->data['clauses'])->map(fn (array $clause) => new Clause($clause))->toArray();
    }

    public function getRiskScore(): int
    {
        return $this->data['risk_score'];
    }

    public function getRiskScoreComment(): string
    {
        return $this->data['risk_score_comment'];
    }
}
