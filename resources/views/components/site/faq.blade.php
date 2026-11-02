{{-- Accordion of questions: :items="[['q' => …, 'a' => …], …]" --}}
@props(['items'])
<div {{ $attributes->class('faq') }}>
    @foreach ($items as $item)
        <details class="faq__item">
            <summary class="faq__question">{{ $item['q'] }}</summary>
            <div class="faq__answer">{{ $item['a'] }}</div>
        </details>
    @endforeach
</div>
