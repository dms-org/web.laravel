<div class="input-group dms-money-input-group" data-field-validation-for="{{ $name }}[amount], {{ $name }}[currency]">
    <span class="input-group-addon">$</span>
    <input
            type="text"
            class="form-control dms-money-input"
            name="{{ $name }}[amount]"
            placeholder="{{ $label }}"
            @if($required) required @endif
            @if($readonly) readonly @endif
            @if($value !== null) value="{{ $value['amount'] }}" @endif
    />
    <select class="form-control dms-currency-input" name="{{ $name }}[currency]" @if($required) required @endif>
        @foreach (\Dms\Common\Structure\Money\Currency::getNameMap() as $code => $label)
            <option value="{{ $code }}" @if($value ? $code === $value['currency'] : $code === $defaultCurrency) selected="selected" @endif>
                {{ $label }} ({{ $code }})
            </option>
        @endforeach
    </select>
</div>