<?php /** @var \Dms\Core\Form\IFieldOptions $options */ ?>
@foreach ($options->getAll() as $option)
    <label class="radio-inline">
        <input
                type="radio"
                name="{{ $name }}"
                @if($required) required @endif
                @if($readonly) readonly @endif
                @if(\Dms\Web\Laravel\Renderer\Form\ValueComparer::areLooselyEqual($option->getValue(), $value)) checked="checked" @endif
        />
        {{ $option->getLabel() }}
    </label>
@endforeach
