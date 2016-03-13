<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<select class="form-control"
        name="{{ $name }}"
        @if($required) required @endif
        @if($readonly) readonly @endif
>
    @foreach ($options->getAll() as $option)
        <option>Please Select...</option>
        <option
                value="{{ $option->getValue() }}"
                @if (\Dms\Web\Laravel\Renderer\Form\ValueComparer::areLooselyEqual($option->getValue(), $value))selected="selected" @endif
        >{{ $option->getLabel() }}</option>
    @endforeach
</select>