@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'help' => null, 'attrs' => ''])
<div class="mb-4">
    <label class="text-sm font-bold block mb-1">{{ $label }} @if($required)<span style="color:#e74c3c">*</span>@endif</label>
    @if($type === 'textarea')
        <textarea name="{{ $name }}" {!! $attrs !!} @if($required) required @endif
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select name="{{ $name }}" {!! $attrs !!} @if($required) required @endif
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">{{ $slot }}</select>
    @else
        <input type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" {!! $attrs !!} @if($required) required @endif
            class="w-full border-2 border-gray-300 rounded-xl px-4 py-2">
    @endif
    @error($name)<p class="text-xs mt-1" style="color:#e74c3c">{{ $message }}</p>@enderror
    @if($help)<p class="text-xs mt-1" style="color:#6b7280">{{ $help }}</p>@endif
</div>
