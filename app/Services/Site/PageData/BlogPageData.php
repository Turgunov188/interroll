<?php

namespace App\Services\Site\PageData;

use App\Enums\Locale;
use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Site\ContentCache;
use App\Services\Site\Presenters\PostPresenter;

/** Blog list: the featured article on top, then the rest with the category filter. */
final class BlogPageData implements PageData
{
    public function __construct(
        private readonly ContentCache $cache,
        private readonly PostPresenter $presenter,
    ) {}

    public function for(Locale $locale): array
    {
        return $this->cache->remember('blog', $locale, function () use ($locale): array {
            $posts = Post::query()->published()->with('category')->latestFirst()->get();
            $featured = $posts->firstWhere('is_featured', true) ?? $posts->first();
            $rest = $posts->reject(fn (Post $post): bool => $featured && $post->is($featured))->values();

            $categories = PostCategory::query()
                ->whereIn('id', $rest->pluck('post_category_id')->filter()->unique())
                ->ordered()
                ->get();

            return [
                'featured' => $featured ? $this->presenter->card($featured, $locale) : null,
                'cats' => $categories->map(fn (PostCategory $c): array => [$c->slug, $c->tr('name', $locale)])->all(),
                'posts' => $rest->map(fn (Post $post): array => $this->presenter->card($post, $locale))->all(),
            ];
        });
    }
}
