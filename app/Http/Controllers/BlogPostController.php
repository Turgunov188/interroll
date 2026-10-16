<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use App\Models\Post;
use App\Services\Site\Presenters\PostPresenter;
use App\Services\Site\SiteResponse;
use Illuminate\Http\Response;

/**
 * /blog/{slug} — an article from the admin, rendered with the article design.
 * Some articles have their own interactive page (config site.blog.custom); they still
 * take the title, author and cover from the admin when the post exists.
 */
class BlogPostController extends Controller
{
    public function show(string $slug, string $lang, SiteResponse $response, PostPresenter $presenter): Response
    {
        $locale = Locale::tryFrom($lang) ?? abort(404);
        $custom = config("site.blog.custom.{$slug}");

        $post = Post::query()->published()->with('category')->where('slug', $slug)->first();
        abort_if(! $post && ! $custom, 404);

        $data = $post ? ['post' => $presenter->page($post, $locale)] : null;

        return $custom
            ? $response->page($locale, $custom, $data)
            : $response->page($locale, config('site.blog.page'), $data, '/'.$post->slug, $post->tr('title', $locale));
    }
}
