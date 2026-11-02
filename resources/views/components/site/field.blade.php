{{--
    Labelled input of a lead form; shows the validation error of its field
    (from the leadForm component, or from the session after a POST without JavaScript).
    <x-site.field name="phone" type="tel" :label="…" placeholder="+998" required />
    <x-site.field name="message" textarea rows="3" full :label="…" />
--}}
@props(['name', 'label', 'type' => 'text', 'textarea' => false, 'rows' => 3, 'full' => false, 'inputClass' => ''])
@php($key = str_replace(['[', ']'], ['.', ''], $name))
<label @class(['field', 'field--full' => $full])>
    <span>{{ $label }}</span>
    @if ($textarea)
        <textarea name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->class(['input', $inputClass]) }} :aria-invalid="!!error('{{ $key }}')">{{ old($name) }}</textarea>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name) }}" {{ $attributes->class(['input', $inputClass]) }} :aria-invalid="!!error('{{ $key }}')">
    @endif
    <span class="field__error" x-show="error('{{ $key }}')" x-text="error('{{ $key }}')" x-cloak></span>
    @error($key)<span class="field__error" x-show="false">{{ $message }}</span>@enderror
</label>
