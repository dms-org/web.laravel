<textarea
        type="{{ $inputType }}"
        name="{{ $name }}"
        placeholder="{{ $label }}"
        @if($required) required @endif
        @if($readonly) readonly @endif

        @if($exactLength !== null)
        minlength="{{ $exactLength }}"
        maxlength="{{ $exactLength }}"
        @else
        @if($minLength !== null) minlength="{{ $minLength }}" @endif
        @if($maxLength !== null) maxlength="{{ $maxLength }}" @endif
        @endif
>{{ $value }}</textarea>