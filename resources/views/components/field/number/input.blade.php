<input
        type="number"
        class="form-control"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($value !== null) value="{{ $value }}" @endif

        @if($min ?? false) min="{{ $min }}" @endif
        @if($max ?? false) min="{{ $max }}" @endif
        @if($greaterThan ?? false) data-greater-than="{{ $greaterThan }}" @endif
        @if($lessThan ?? false) data-less-than="{{ $lessThan }}" @endif
        @if($maxDecimalPlaces ?? false) step="{{ pow(.1, $maxDecimalPlaces) }}" data-max-decimal-places="{{ $maxDecimalPlaces }}" @endif
/>