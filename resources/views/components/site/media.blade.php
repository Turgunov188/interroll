{{--
    Photo slot: the striped placeholder with its caption until a photo is uploaded in the admin.
    <x-site.media :src="$project->cover" caption="Прокатные валы" style="--media-h: 240px" />
--}}
@props(['src' => null, 'caption' => null, 'alt' => '', 'tone' => null, 'fit' => 'cover', 'lazy' => true])
<div {{ $attributes->class(['media', 'media--'.$tone => $tone, 'media--contain' => $fit === 'contain']) }}>
    @if ($caption)<span>[{{ $caption }}]</span>@endif
    {{ $slot }}
    @if ($src)<img src="{{ $src }}" alt="{{ $alt }}" @if ($lazy) loading="lazy" decoding="async" @endif>@endif
</div>
