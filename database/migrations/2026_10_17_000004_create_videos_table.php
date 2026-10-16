<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** /video: films about the factory (YouTube link or an uploaded .mp4) and short clips. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 16)->default('main');      // App\Enums\VideoKind
            $table->json('title');
            $table->json('category')->nullable();
            $table->json('description')->nullable();
            $table->string('url')->nullable();                 // YouTube / Instagram / Telegram link
            $table->string('file')->nullable();                // uploaded .mp4 (main videos)
            $table->string('poster')->nullable();
            $table->string('duration', 16)->nullable();        // "3:20"
            $table->json('chapters')->nullable();              // [{"at": "0:45", "label": {"ru": …}}]
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
