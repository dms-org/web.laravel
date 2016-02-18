<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<?php /** @var array $value */ ?>
<?php $valuesAsKeys = $value ? array_fill_keys($value, true) : []; ?>

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
                    @if(isset($valuesAsKeys[$option->getValue()])) checked="checked" @endif
            />
            {{ $option->getLabel() }}
        </label>
    @else
        <p class="help-block">No options are available</p>
    @endforeach
</div>