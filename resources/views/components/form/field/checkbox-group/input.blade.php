<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<div class="list-of-checkboxes"
     @if($exactElements !== null)
     data-min-elements="{{ $exactElements }}"
     data-max-elements="{{ $exactElements }}"
     @else
     @if($minElements !== null) data-min-elements="{{ $minElements }}" @endif
     @if($maxElements !== null) data-max-elements="{{ $maxElements }}" @endif
    @endif
>
    @foreach ($options as $option)
        <label class="checkbox-inline">
            <input
                    type="checkbox"
                    name="{{ $name }}[]"
                    @if($required) required @endif
                    @if($readonly) readonly @endif
                    @if(\Dms\Web\Laravel\Renderer\Form\ValueComparer::areLooselyEqual($option->getValue(), $value)) checked="checked" @endif
            />
            {{ $option->getLabel() }}
        </label>
    @endforeach
</div>