<?php

namespace App\Models;

use App\Models\Enums\ReviewSource;
use App\Models\Enums\ReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $guarded = [
        //
    ];

    protected $casts = [
        'status' => ReviewStatus::class,
        'source' => ReviewSource::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clauses(): HasMany
    {
        return $this->hasMany(ContractClause::class);
    }
}
