<div
        class="dms-inner-module"
        data-name="{{ $name }}"
        data-root-url="{{ $rootUrl }}"
        @if($required) data-required="1" @endif
        @if($readonly) data-readonly="1" @endif
        @if($value !== null) data-value="{{ json_encode($value) }}" @endif
>
        {!! $moduleContent !!}

        <div class="dms-inner-module-form"></div>
</div>