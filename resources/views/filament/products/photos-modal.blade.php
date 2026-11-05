{{-- All photos of a product, opened from the products table. Data: $photos (list of URLs), $name. --}}
<div x-data="{ i: 0, count: {{ count($photos) }} }"
     x-on:keydown.right.window="i = (i + 1) % count"
     x-on:keydown.left.window="i = (i + count - 1) % count"
     style="display: grid; gap: 14px;">
    <div style="position: relative; background: #fff; border-radius: 14px; border: 1px solid rgb(0 0 0 / 0.08);">
        @foreach ($photos as $index => $url)
            <img x-show="i === {{ $index }}" @if ($index) x-cloak @endif src="{{ $url }}" alt="{{ $name }}"
                 style="display: block; width: 100%; height: min(70vh, 620px); object-fit: contain; padding: 16px;">
        @endforeach

        @if (count($photos) > 1)
            @foreach ([['‹', 'i = (i + count - 1) % count', 'left'], ['›', 'i = (i + 1) % count', 'right']] as [$arrow, $go, $side])
                <button type="button" x-on:click="{{ $go }}"
                        style="position: absolute; top: 50%; {{ $side }}: 10px; transform: translateY(-50%); width: 40px; height: 40px; border-radius: 999px; border: 0; background: rgb(0 0 0 / 0.55); color: #fff; font-size: 24px; line-height: 1; cursor: pointer;">{{ $arrow }}</button>
            @endforeach
            <span x-text="`${i + 1} / ${count}`"
                  style="position: absolute; right: 12px; bottom: 10px; padding: 2px 8px; border-radius: 999px; background: rgb(0 0 0 / 0.55); color: #fff; font-size: 12px;"></span>
        @endif
    </div>

    @if (count($photos) > 1)
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @foreach ($photos as $index => $url)
                <button type="button" x-on:click="i = {{ $index }}"
                        :style="`width: 64px; height: 64px; padding: 4px; background: #fff; border-radius: 10px; cursor: pointer; border: 2px solid ${i === {{ $index }} ? 'rgb(245 158 11)' : 'rgb(0 0 0 / 0.08)'};`">
                    <img src="{{ $url }}" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                </button>
            @endforeach
        </div>
    @endif
</div>
