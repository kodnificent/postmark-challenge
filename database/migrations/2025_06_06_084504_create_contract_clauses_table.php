<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('reviews')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('comment')->nullable();
            $table->tinyInteger('risk_score')->default(0);
            $table->text('risk_score_comment')->nullable();
            $table->timestamps();
        });
    }
};
