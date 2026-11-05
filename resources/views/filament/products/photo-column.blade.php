{{--
    Products table: the main photo. Hover shows it large next to the row; a click opens all photos
    (ProductResource → the column's «photos» action). Inline styles: the admin panel uses Filament's
    prebuilt CSS, so Tailwind classes that are not already in it would not apply.
--}}
@php
    /** @var \App\Models\Product $product */
    $product = $getRecord();
    $url = $product->mainPhoto?->url();
@endphp
@if ($url)
    <div
        x-data="{
            open: false, x: 0, y: 0,
            show() {
                const r = this.$el.getBoundingClientRect(), size = 320;
                this.x = r.right + size + 24 < window.innerWidth ? r.right + 12 : r.left - size - 12;
                this.y = Math.max(8, Math.min(r.top + r.height / 2 - size / 2, window.innerHeight - size - 8));
                this.open = true;
            },
        }"
        x-on:mouseenter="show()"
        x-on:mouseleave="open = false"
        style="width: 48px; height: 48px; cursor: zoom-in;"
    >
        <img src="{{ $url }}" alt="{{ $product->sku }}" loading="lazy"
             style="width: 48px; height: 48px; object-fit: contain; background: #fff; border: 1px solid rgb(0 0 0 / 0.08); border-radius: 8px;">

        <template x-teleport="body">
            <div x-show="open" x-cloak x-transition.opacity.duration.150ms
                 :style="`position: fixed; left: ${x}px; top: ${y}px; z-index: 60; pointer-events: none;`">
                <img src="{{ $url }}" alt=""
                     style="display: block; width: 320px; height: 320px; object-fit: contain; padding: 12px; background: #fff; border-radius: 14px; box-shadow: 0 20px 50px -12px rgb(0 0 0 / 0.35), 0 0 0 1px rgb(0 0 0 / 0.06);">
            </div>
        </template>
    </div>
@else
    <div style="width: 48px; height: 48px; border: 1px dashed rgb(0 0 0 / 0.15); border-radius: 8px;"></div>
@endif
