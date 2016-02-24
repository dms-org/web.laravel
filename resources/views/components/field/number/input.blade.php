<input
        type="number"
        class="form-control"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        @if($value !== null) value="{{ $value }}" @endif

        @if(($min ?? null) !== null) min="{{ $min }}" @endif
        @if(($max ?? null) !== null) min="{{ $max }}" @endif
        @if(($decimalNumber ?? null) !== null) data-decimal-number="1" @endif
        @if(($greaterThan ?? null) !== null) data-greater-than="{{ $greaterThan }}" @endif
        @if(($lessThan ?? null) !== null) data-less-than="{{ $lessThan }}" @endif
        @if(($maxDecimalPlaces ?? null) !== null) step="{{ pow(.1, $maxDecimalPlaces) }}" data-max-decimal-places="{{ $maxDecimalPlaces }}" @endif
/>