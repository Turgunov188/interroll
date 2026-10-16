<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Completed work shown on /projects: problem → solution → result. Text columns are {"ru": …, "uz": …}. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('industry_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('client')->nullable();
            $table->json('short')->nullable();
            $table->json('problem')->nullable();
            $table->json('solution')->nullable();
            $table->json('result')->nullable();
            $table->json('kpis')->nullable();          // [{"value": "3×", "label": {"ru": …}}]
            $table->string('cover')->nullable();
            $table->string('photo_before')->nullable();
            $table->string('photo_after')->nullable();
            $table->json('quote')->nullable();
            $table->string('quote_author')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
