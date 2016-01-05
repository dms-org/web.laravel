<input
        type="number"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($value !== null) value="{{ $value }}" @endif

        @if($min !== null) min="{{ $min }}" @endif
        @if($max !== null) min="{{ $max }}" @endif
        @if($greaterThan !== null) data-greater-than="{{ $greaterThan }}" @endif
        @if($lessThan !== null) data-less-than="{{ $lessThan }}" @endif
        @if($maxDecimalPlaces !== null) step="{{ pow(.1, $maxDecimalPlaces) }}" data-max-decimal-places="{{ $maxDecimalPlaces }}" @endif
/>