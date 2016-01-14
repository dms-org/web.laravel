<div class='input-group date-or-time-range'>
    <input
            type="text"
            class="date-or-time start-input"
            name="{{ $name }}[start]"
            placeholder="Start"
            @if($required) required @endif
            @if($readonly) readonly @endif
            @if($value !== null) value="{{ $value['start']->format($format) }}" @endif
            data-date-format="{{ $format }}"

            @if($min !== null) data-min-date="{{ $min->getTimestamp() * 1000 }}" @endif
            @if($max !== null) data-max-date="{{ $max->getTimestamp() * 1000 }}" @endif
    />
    <span class="input-group-addon">to</span>
    <input
            type="text"
            class="date-or-time end-input"
            name="{{ $name }}[end]"
            placeholder="End"
            @if($required) required @endif
            @if($readonly) readonly @endif
            @if($value !== null) value="{{ $value['end']->format($format) }}" @endif
            data-date-format="{{ $format }}"
            data-dont-use-current="1"

            @if($min !== null) data-min-date="{{ $min->getTimestamp() * 1000 }}" @endif
            @if($max !== null) data-max-date="{{ $max->getTimestamp() * 1000 }}" @endif
    />
</div>