<?php

namespace App\Services\Site\Presenters;

use App\Enums\Locale;
use App\Enums\MediaType;
use App\Models\Post;
use App\Services\Site\SiteUrl;
use App\Support\SafeHtml;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

/** Blog post → the shapes the blog list and article templates expect (build-tools/dynamic_patches.py). */
final class PostPresenter
{
    public function __construct(private readonly SiteUrl $urls) {}

    /** @return array<string, mixed> one card in the blog list */
    public function card(Post $post, Locale $locale): array
    {
        $body = (string) $post->tr('body', $locale);

        return [
            'k' => $post->category?->slug ?? '',
            'cat' => (string) $post->category?->tr('name', $locale),
            't' => (string) $post->tr('title', $locale),
            'd' => (string) $post->tr('excerpt', $locale),
            'min' => $post->readMinutes($body),
            'photo' => (string) ($post->tr('cover_caption', $locale) ?: $post->tr('title', $locale)),
            'img' => $post->fileUrl('cover'),
            'url' => $this->urls->post($locale, $post->slug),
        ];
    }

    /** @return array<string, mixed> the article page */
    public function page(Post $post, Locale $locale): array
    {
        $clean = SafeHtml::clean($this->editorHtml((string) $post->tr('body', $locale)));

        return $this->card($post, $locale) + [
            'author' => (string) $post->tr('author', $locale),
            'caption' => (string) $post->tr('cover_caption', $locale),
            'body' => $clean['html'],
            'toc' => $clean['toc'],
            'date' => $post->published_at?->format('d.m.Y') ?? '',
        ];
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
