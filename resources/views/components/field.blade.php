@props(['name', 'label', 'type' => 'text', 'options' => null, 'value' => null, 'required' => true])

@php
    $current = old($name, $value);
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if ($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            {{ $attributes }}
            class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none {{ $errors->has($name) ? 'border-red-400' : 'border-gray-300 focus:border-accent' }}"
        >
            <option value="">Select…</option>
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'file')
        <input
            type="file"
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            {{ $attributes }}
            class="mt-1 block w-full text-sm text-gray-700 {{ $errors->has($name) ? 'border-red-400' : '' }}"
        >
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $current }}"
            @if ($required) required @endif
            {{ $attributes }}
            class="mt-1 w-full rounded-md border px-3 py-2 text-sm focus:outline-none {{ $errors->has($name) ? 'border-red-400' : 'border-gray-300 focus:border-accent' }}"
        >
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
