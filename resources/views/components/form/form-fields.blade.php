<?php /** @var string[][] $groupedFields */ ?>
<div
        class="dms-form-fields"
        @if ($equalFields) data-equal-fields="{{ json_encode($equalFields) }}" @endif
        @if ($greaterThanFields) data-greater-than-fields="{{ json_encode($greaterThanFields) }}" @endif
        @if ($greaterThanOrEqualFields) data-greater-than-or-eqaul-fields="{{ json_encode($greaterThanOrEqualFields) }}" @endif
        @if ($lessThanFields) data-less-than-fields="{{ json_encode($lessThanFields) }}" @endif
        @if ($lessThanOrEqualFields) data-less-than-or-equal-fields="{{ json_encode($lessThanOrEqualFields) }}" @endif
>
    @foreach($groupedFields as $groupTitle => $fields)
        <fieldset class="dms-form-fieldset">
            <legend>{{ $groupTitle }}</legend>
            @foreach($fields as $label => $field)
                <div class="form-group" data-field-name="{{ $field['name'] }}">
                    <label data-for="{{ $field['name'] }}">{{ $label }}</label>
                    {!! $field['content'] !!}
                </div>
            @endforeach
        </fieldset>
    @endforeach
</div>