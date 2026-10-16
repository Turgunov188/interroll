<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Blog «Советы инженера»: categories and posts. Text columns are {"ru": …, "uz": …}. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable();             // HTML from the rich editor, per language
            $table->json('author')->nullable();
            $table->string('cover')->nullable();
            $table->json('cover_caption')->nullable();
            $table->unsignedSmallInteger('read_minutes')->nullable();   // empty → counted from the text
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('seo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};
