<?php

namespace App\Models\Enums;

enum ReviewSource: string
{
    case EMAIL = 'email';
    case APP = 'app';

    public function getLabel(): string
    {
        return match ($this) {
            self::EMAIL => 'Email',
            self::APP => 'App',
        };
    }
}
