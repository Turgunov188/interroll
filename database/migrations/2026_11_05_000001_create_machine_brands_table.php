<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Spare parts for textile stenters fit one machine make only (a Monforts clip does not fit a Bruckner),
 * so buyers look for parts by their machine first. Brand names are the same in every language.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machine_brands', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('machine_brand_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('machine_brand_id');
        });

        Schema::dropIfExists('machine_brands');
    }
};
