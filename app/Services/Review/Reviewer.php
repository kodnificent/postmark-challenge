<?php

namespace App\Services\Review;

interface Reviewer
{
    public function analyze(string $content): Output;
}
