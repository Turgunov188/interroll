<?php

namespace Database\Seeders;

use App\Models\Industry;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Video;
use App\Models\Project;
use App\Services\Site\ContentCache;
use Illuminate\Database\Seeder;

/**
 * Starting content from the approved design — projects, blog posts and videos in 4 languages.
 * Everything is created as a hidden draft: the gaps like [__] must be filled with real numbers
 * before «Показывать на сайте» is switched on. Existing rows (same slug) are left untouched,
 * so the factory's edits are never overwritten.
 *
 *   php artisan db:seed --class=ContentSeeder
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = require __DIR__.'/data/content.php';
        $industries = Industry::query()->pluck('id', 'slug');

        foreach ($data['projects'] as $row) {
            $industry = $row['industry'];
            unset($row['industry']);

            Project::query()->firstOrCreate(['slug' => $row['slug']], $row + [
                'industry_id' => $industries[$industry] ?? null,
                'is_published' => false,
            ]);
        }

        foreach ($data['post_categories'] as $row) {
            PostCategory::query()->firstOrCreate(['slug' => $row['slug']], $row);
        }
        $categories = PostCategory::query()->pluck('id', 'slug');

        foreach ($data['posts'] as $row) {
            $category = $row['category'];
            unset($row['category']);

            Post::query()->firstOrCreate(['slug' => $row['slug']], $row + [
                'post_category_id' => $categories[$category] ?? null,
                'is_published' => false,
            ]);
        }

        // videos have no slug: added only while the table is still empty
        if (! Video::query()->exists()) {
            foreach ($data['videos'] as $row) {
                Video::query()->create($row + ['is_published' => false]);
            }
        }

        app(ContentCache::class)->flush();
    }
}
