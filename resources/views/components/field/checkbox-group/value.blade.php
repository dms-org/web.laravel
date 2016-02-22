<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<?php /** @var array $value */ ?>
<?php $valuesAsKeys = $value ? array_fill_keys($value, true) : []; ?>
<ul class="dms-display-list list-group">
    @foreach ($options as $option)
        @if(isset($valuesAsKeys[$option->getValue()]))
            <li class="list-group-item">{{ $option->getLabel() }}</li>
        @endif
    @endforeach
</ul>