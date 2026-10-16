<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog: categories (tree), industries (sohalar), products.
 * Translatable text is stored as JSON: {"uz": "...", "ru": "..."}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('seo')->nullable(); // {"uz":{"title":..,"description":..},"ru":{...}}
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku', 64)->unique();          // artikul
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('short')->nullable();            // one-line summary for cards
            $table->json('description')->nullable();

            $table->string('material', 20)->default('pu'); // pu | rubber | other
            $table->unsignedSmallInteger('hardness')->nullable(); // e.g. 90
            $table->string('hardness_scale', 2)->nullable();       // A | D  (Shore)
            $table->json('specs')->nullable();            // [{"key":"diameter","value":"120","unit":"mm"}, ...]

            $table->json('images')->nullable();           // ["products/abc.webp", ...]
            $table->string('drawing')->nullable();        // PDF/DWG preview of the drawing

            $table->decimal('price', 14, 2)->nullable();
            $table->boolean('price_on_request')->default(true);
            $table->boolean('made_to_order')->default(true); // "chizmangiz bo'yicha"

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->json('seo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active', 'sort']);
            $table->index(['material', 'hardness']);
        });

        Schema::create('industry_product', function (Blueprint $table) {
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['industry_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('industry_product');
        Schema::dropIfExists('products');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('categories');
    }
};
