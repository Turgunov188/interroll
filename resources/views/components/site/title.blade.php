{{--
    Heading with an accented (yellow) last part:
    <x-site.title tag="h1" class="display" :lines="['Вал. Колесо. Деталь.']" accent="По вашему чертежу." />
--}}
@props(['tag' => 'h2', 'lines' => [], 'accent' => null])
<{{ $tag }} {{ $attributes->class(['h2' => ! $attributes->has('class')]) }}>
    @foreach ((array) $lines as $line)
        {{ $line }}@if (! $loop->last || $accent)<br>@endif
    @endforeach
    @if ($accent)<span class="hl">{{ $accent }}</span>@endif
</{{ $tag }}>
