<?php

namespace App\Services\Review;

class Clause
{
    public function __construct(
        protected array $data
    ) {}

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getComment(): string
    {
        return $this->data['comment'];
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
