{{-- A product on the home page (industry tabs). --}}
@props(['product'])
<a href="{{ $product->url }}" {{ $attributes->class('card') }}>
    <x-site.media :src="$product->image" :caption="$product->name" :alt="$product->name" class="product-card__photo" />
    <span class="card__body">
        <span class="meta">{{ $product->code }}</span>
        <span class="card__title">{{ $product->name }}</span>
        <span class="card__text">{{ $product->summary }}</span>
        <span class="card__more">{{ mb_strtoupper($site->t('common.price_on_request')) }} →</span>
    </span>
</a>
