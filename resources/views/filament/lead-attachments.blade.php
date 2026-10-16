{{-- Customer files on a lead: images as previews, other files (PDF, DWG, ZIP…) as download cards. --}}
@php($files = app(\App\Services\Leads\LeadAttachments::class)->for($getRecord()))
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px;">
    @foreach ($files as $file)
        <a href="{{ $file['url'] ?? '#' }}" target="_blank" rel="noopener"
           style="display: flex; flex-direction: column; border: 1px solid rgb(127 127 127 / .25); border-radius: .75rem; overflow: hidden; text-decoration: none; color: inherit; {{ $file['url'] ? '' : 'opacity: .5; pointer-events: none;' }}">
            @if ($file['isImage'])
                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" loading="lazy"
                     style="width: 100%; height: 140px; object-fit: cover; background: rgb(127 127 127 / .08);">
            @else
                <div style="height: 140px; display: flex; align-items: center; justify-content: center; background: rgb(127 127 127 / .08);">
                    <span style="font: 700 1.25rem/1 ui-monospace, monospace; letter-spacing: .05em; padding: .5rem .75rem; border: 2px solid currentColor; border-radius: .375rem; opacity: .7;">{{ $file['extension'] }}</span>
                </div>
            @endif
            <div style="padding: .5rem .75rem; display: flex; justify-content: space-between; gap: .5rem; font-size: .8125rem;">
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
                <span style="opacity: .6; white-space: nowrap;">{{ $file['size'] }}</span>
            </div>
        </a>
    @endforeach
</div>
