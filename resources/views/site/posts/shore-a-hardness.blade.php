{{-- Body of «Как выбрать твёрдость по Шору A?» with the interactive hardness scale (texts: lang/*/shore.php). --}}
@php($s = $site->t('shore'))
<h2 id="what">{{ $s['sections']['what'] }}</h2>
@foreach ($s['what'] as $paragraph)<p>{{ $paragraph }}</p>@endforeach

<h2 id="scale">{{ $s['sections']['scale'] }}</h2>
<p>{{ $s['scale']['intro'] }}</p>
<div class="shore-scale" x-data="shoreScale(@js($s['scale']['points']))">
    <div class="cluster cluster--between"><span class="eyebrow">{{ $s['scale']['label'] }}</span><b class="shore-scale__value"><span x-text="value">85</span> <small>A</small></b></div>
    <div class="shore-scale__bar"><span :style="{ left: position + '%' }"></span></div>
    <input type="range" class="range" min="20" max="98" x-model.number="value" aria-label="{{ $s['scale']['aria'] }}">
    <div class="shore-scale__cards">
        <div class="shore-scale__card"><span class="label">{{ $s['scale']['feels'] }}</span><b x-text="point[1]"></b></div>
        <div class="shore-scale__card shore-scale__card--accent"><span class="label text-ink">{{ $s['scale']['used'] }}</span><b x-text="point[2]"></b></div>
    </div>
    <span class="meta">{{ $s['scale']['note'] }}</span>
</div>

<h2 id="mistake">{{ $s['sections']['mistake'] }}</h2>
<p>{{ $s['mistake']['intro'] }}</p>
<ul>@foreach ($s['mistake']['items'] as $item)<li>{{ $item }}</li>@endforeach</ul>
<p>{{ $s['mistake']['outro'] }}</p>

<h2 id="choice">{{ $s['sections']['choice'] }}</h2>
<p>{{ $s['choice']['intro'] }}</p>
<ul>@foreach ($s['choice']['items'] as [$range, $text])<li><b>{{ $range }}</b> {{ $text }}</li>@endforeach</ul>
<p>{{ $s['choice']['outro'] }}</p>

<h2 id="summary">{{ $s['sections']['summary'] }}</h2>
<p>{{ $s['summary'] }}</p>
