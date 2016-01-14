<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<?php /** @var array $value */ ?>
<?php $valuesAsKeys = $value ? array_fill_keys($value, true) : []; ?>
<?php $labels = []; ?>
@foreach ($options as $option)
    @if(isset($valuesAsKeys[$option->getValue()]))
        <?php $labels[] = $option->getLabel() ?>
    @endif
@endforeach
{{ implode(', ', $labels) }}