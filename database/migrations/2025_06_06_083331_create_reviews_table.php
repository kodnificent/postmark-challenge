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
            $table->string('file_disk')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable();
            $table->string('title')->nullable();
            $table->string('summary')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('source')->nullable(); // email, ui
            $table->tinyInteger('risk_score')->default(0); // 0-100
            $table->timestamps();
        });
    }
};
