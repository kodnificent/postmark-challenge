<?php

namespace App\Services\Review;

class Manager
{
    public function driver(string $driver): Reviewer
    {
        return match ($driver) {
            'openai' => new OpenAi(config('services.openai')),
        };
    }
}
