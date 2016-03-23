<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<?php /** @var array $value */ ?>
<?php $valuesAsKeys = $value ? array_fill_keys($value, true) : []; ?>
@if (count($valuesAsKeys) === 0)
    @include('dms::components.field.null.value')
@else
    <ul class="dms-display-list list-group">
        @foreach ($options as $option)
            @if(isset($valuesAsKeys[$option->getValue()]))
                <li class="list-group-item">
                    @if($urlCallback ?? false)
                        <a href="{{ $urlCallback($option->getValue()) }}">{{ $option->getLabel() }}</a>
                    @else
                        {{ $option->getLabel() }}
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
@endif