{{-- File field of a lead form: a drop zone that lists the chosen files. --}}
@props(['label', 'hint' => null, 'accept' => null, 'multiple' => true])
<label class="field field--full">
    <span>{{ $label }}</span>
    <span class="dropzone" :class="{ 'is-over': dragging }"
          @dragover.prevent="dragging = true" @dragleave="dragging = false" @drop.prevent="dropFiles($event)">
        <x-site.icon path="M12 16V4M7 9l5-5 5 5M4 20h16" />
        @if ($hint)<span>{{ $hint }}</span>@endif
        <input type="file" name="attachments[]" @if ($multiple) multiple @endif @if ($accept) accept="{{ $accept }}" @endif @change="pickFiles($event)">
        <span class="dropzone__files" x-show="files.length" x-text="files.join(', ')" x-cloak></span>
    </span>
    <span class="field__error" x-show="error('attachments')" x-text="error('attachments')" x-cloak></span>
</label>
