<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<select
        name="{{ $name }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
        multiple="multiple"
>
    @foreach ($options as $option)
        <option
                value="{{ $option->getValue() }}"
                @if (\Dms\Web\Laravel\Renderer\Form\ValueComparer::areLooselyEqual($option->getValue(), $value))selected="selected" @endif
        >{{ $option->getLabel() }}</option>
    @endforeach
</select>