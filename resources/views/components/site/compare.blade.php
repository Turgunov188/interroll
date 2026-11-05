{{-- Before / after slider. Photos come from the admin; until then a drawn worn / recoated roller shows. --}}
@props(['before' => null, 'after' => null, 'beforeCaption', 'afterCaption', 'artTone' => 'conveyor'])
<div {{ $attributes->class('compare') }} x-data="compare(50)">
    <x-site.media class="compare__layer" tone="warm" :src="$after" :caption="$afterCaption" art="restored" :art-tone="$artTone" />
    <x-site.media class="compare__layer compare__layer--before" tone="steel" :src="$before" :caption="$beforeCaption" art="worn" :art-tone="$artTone" />
    <div class="compare__line" aria-hidden="true"><span class="compare__handle">↔</span></div>
    <span class="compare__tag compare__tag--before">{{ $site->t('common.before') }}</span>
    <span class="compare__tag compare__tag--after">{{ $site->t('common.after') }}</span>
    <input class="compare__range" type="range" min="0" max="100" x-model.number="split" aria-label="{{ $site->t('common.compare') }}">
</div>
