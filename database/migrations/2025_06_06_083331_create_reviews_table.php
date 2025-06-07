<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('content');
            $table->string('status')->default('pending');
            $table->string('source');
            $table->tinyInteger('risk_score')->default(0); // 0-100
            $table->text('risk_score_comment')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
