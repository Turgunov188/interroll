<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

/**
 * Cleans HTML written in the admin's rich editor before it is put on the public site:
 * only text formatting, lists, links, images and tables survive; scripts, styles, event
 * handlers and javascript: links are dropped. Headings get ids for the table of contents.
 */
final class SafeHtml
{
    private const TAGS = [
        'p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'mark',
        'ul', 'ol', 'li', 'blockquote', 'hr', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'code', 'pre',
    ];

    private const ATTRIBUTES = [
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'width', 'height'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    /** Tags whose content is dropped together with them. */
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'template'];

    /**
     * @return array{html: string, toc: list<array{href: string, t: string}>}
     */
    public static function clean(?string $html): array
    {
        if (blank($html)) {
            return ['html' => '', 'toc' => []];
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="safe-root">'.$html.'</div>', LIBXML_NONET | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('safe-root');
        if (! $root) {
            return ['html' => '', 'toc' => []];
        }

        self::walk($root);

        $toc = [];
        $used = [];
        foreach (iterator_to_array($root->getElementsByTagName('h2')) as $i => $h2) {
            $text = trim($h2->textContent);
            if ($text === '') {
                continue;
            }
            $id = Str::slug($text) ?: 'section';
            $id = isset($used[$id]) ? $id.'-'.(++$used[$id]) : $id;
            $used[$id] ??= 1;
            $h2->setAttribute('id', $id);
            $h2->setAttribute('data-sec', (string) count($toc));
            $toc[] = ['href' => '#'.$id, 't' => $text];
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return ['html' => trim($out), 'toc' => $toc];
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if (! in_array($tag, self::TAGS, true)) {
                // unknown wrapper (div, span, font…): keep its content, drop the tag
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            self::attributes($child, $tag);
        }
    }

    private static function attributes(DOMElement $el, string $tag): void
    {
        $allowed = self::ATTRIBUTES[$tag] ?? [];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (! in_array($name, $allowed, true) || (in_array($name, ['href', 'src'], true) && ! self::safeUrl($attr->value))) {
                $el->removeAttribute($attr->name);
            }
        }

        if ($tag === 'a' && $el->hasAttribute('href') && preg_match('#^https?://#i', $el->getAttribute('href'))) {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener');
        }

        if ($tag === 'img') {
            $el->setAttribute('loading', 'lazy');
        }
    }

    private static function safeUrl(string $url): bool
    {
        $url = trim($url);

        return $url !== '' && (bool) preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $url) && ! str_starts_with($url, '//');
    }
}
