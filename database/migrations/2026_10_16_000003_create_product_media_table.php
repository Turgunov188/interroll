<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Product files move from two columns (products.images JSON, products.drawing)
 * to their own table, typed as photo / drawing / model3d / document.
 * Existing paths are copied over, so nothing uploaded so far is lost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);                       // App\Enums\MediaType
            $table->string('disk', 20)->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable();   // bytes
            $table->json('alt')->nullable();                  // {"ru": "...", "uz": "..."} caption / alt text
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'type', 'sort']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('configurator', 20)->nullable()->after('made_to_order'); // App\Enums\Configurator
        });

        $now = now();

        DB::table('products')->select(['id', 'images', 'drawing'])->orderBy('id')->chunkById(200, function ($products) use ($now) {
            $rows = [];

            foreach ($products as $product) {
                foreach (array_values(json_decode($product->images ?? '[]', true) ?: []) as $i => $path) {
                    $rows[] = ['product_id' => $product->id, 'type' => 'photo', 'disk' => 'public', 'path' => $path, 'sort' => $i, 'created_at' => $now, 'updated_at' => $now];
                }

                if ($product->drawing) {
                    $rows[] = ['product_id' => $product->id, 'type' => 'drawing', 'disk' => 'public', 'path' => $product->drawing, 'sort' => 0, 'created_at' => $now, 'updated_at' => $now];
                }
            }

            if ($rows !== []) {
                DB::table('product_media')->insert($rows);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['images', 'drawing']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('images')->nullable();
            $table->string('drawing')->nullable();
        });

        DB::table('product_media')->orderBy('product_id')->orderBy('sort')->get()
            ->groupBy('product_id')
            ->each(function ($media, $productId) {
                DB::table('products')->where('id', $productId)->update([
                    'images' => json_encode($media->where('type', 'photo')->pluck('path')->values()->all()),
                    'drawing' => $media->firstWhere('type', 'drawing')?->path,
                ]);
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('configurator');
        });

        Schema::dropIfExists('product_media');
    }
};
