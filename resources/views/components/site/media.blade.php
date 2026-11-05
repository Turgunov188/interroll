{{--
    Photo slot: the striped placeholder with its caption until a photo is uploaded in the admin.
    <x-site.media :src="$project->cover" caption="Прокатные валы" style="--media-h: 240px" />
    Before / after slots pass art="worn|restored" (+ art-tone): without a photo they show a drawn roller,
    captioned as an illustration so nobody takes it for a photo of real work.
--}}
@props(['src' => null, 'caption' => null, 'alt' => '', 'tone' => null, 'fit' => 'cover', 'lazy' => true, 'art' => null, 'artTone' => 'conveyor'])
@php($drawn = ! $src && $art)
<div {{ $attributes->class(['media', 'media--'.$tone => $tone, 'media--contain' => $fit === 'contain', 'media--art media--art-'.$art => $drawn]) }}>
    @if ($drawn)<x-site.roller-art class="media__art" :state="$art" :tone="$artTone" />@endif
    @if ($caption)<span>[{{ $drawn ? $site->t('common.illustration').' · ' : '' }}{{ $caption }}]</span>@endif
    {{ $slot }}
    @if ($src)<img src="{{ $src }}" alt="{{ $alt }}" @if ($lazy) loading="lazy" decoding="async" @endif>@endif
</div>
