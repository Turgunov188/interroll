<?php

namespace App\Services\Site\Presenters;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Models\Post;
use App\Services\Site\SiteUrl;
use App\Services\Site\ViewModels\PostCard;
use App\Services\Site\ViewModels\PostPage;
use App\Support\SafeHtml;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

/** Blog post model → the blog list card and the article page. */
final class PostPresenter
{
    public function __construct(private readonly SiteUrl $urls) {}

    public function card(Post $post, Locale $locale): PostCard
    {
        return new PostCard(
            slug: $post->slug,
            category: $post->category?->slug ?? '',
            categoryName: (string) $post->category?->tr('name', $locale),
            title: (string) $post->tr('title', $locale),
            excerpt: (string) $post->tr('excerpt', $locale),
            minutes: $post->readMinutes((string) $post->tr('body', $locale)),
            coverCaption: (string) ($post->tr('cover_caption', $locale) ?: $post->tr('title', $locale)),
            cover: $post->fileUrl('cover'),
            url: $this->urls->post($locale, $post->slug),
        );
    }

    public function page(Post $post, Locale $locale): PostPage
    {
        $clean = SafeHtml::clean($this->editorHtml((string) $post->tr('body', $locale)));

        return new PostPage(
            card: $this->card($post, $locale),
            author: (string) $post->tr('author', $locale),
            body: $clean['html'],
            toc: $clean['toc'],
            date: $post->published_at?->format('d.m.Y'),
        );
    }

    /** Images uploaded in the rich editor are stored by id; the renderer turns them into public URLs. */
    private function editorHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        return RichContentRenderer::make($html)
            ->fileAttachmentsDisk(MediaType::DISK)
            ->fileAttachmentsVisibility('public')
            ->toUnsafeHtml();
    }
}
