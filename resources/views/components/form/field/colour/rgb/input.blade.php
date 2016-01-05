<input
        type="text"
        class="dms-colour-input dms-colour-input-rgb"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($value !== null) value="{{ $value }}" @endif
/>