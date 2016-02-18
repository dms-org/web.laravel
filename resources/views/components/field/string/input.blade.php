<input
        type="{{ $type }}"
        class="form-control"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($value !== null) value="{{ $value }}" @endif

        @if($exactLength ?? false)
        minlength="{{ $exactLength }}"
        maxlength="{{ $exactLength }}"
        @else
        @if($minLength !== null) minlength="{{ $minLength }}" @endif
        @if($maxLength !== null) maxlength="{{ $maxLength }}" @endif
        @endif
/>