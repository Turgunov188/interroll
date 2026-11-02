<?php

namespace App\Http\Controllers;

use App\Enums\Locale;
use App\Models\Post;
use App\Services\Site\Presenters\PostPresenter;
use App\Services\Site\SiteResponse;
use Illuminate\Http\Response;

/**
 * /blog/{slug} — an article from the admin. Some articles have their own interactive body
 * (config site.blog.custom); the page still takes the title, author and cover from the admin.
 */
class BlogPostController extends Controller
{
    public function show(string $slug, string $lang, SiteResponse $response, PostPresenter $presenter): Response
    {
        $locale = Locale::tryFrom($lang) ?? abort(404);
        $body = config("site.blog.custom.{$slug}");

        $post = Post::query()->published()->with('category')->where('slug', $slug)->first();
        abort_if(! $post && ! $body, 404);

        return $response->page($locale, 'post', [
            'post' => $post ? $presenter->page($post, $locale) : null,
            'bodyView' => $body,
        ]);
    }
}
