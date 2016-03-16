<span class="dms-display-money">
    {{ number_format($value->asString(), $value->getCurrency()->getDefaultFractionDigits()) . ' ' . $value->getCurrency()->getCurrencyCode() }}
</span>