<?php /** @var \Dms\Core\Form\IFieldOption[] $options */ ?>
<?php /** @var array $value */ ?>
@if (count($value) === 0)
    @include('dms::components.field.null.value')
@else
    <ul class="dms-display-list list-group">
        @foreach ($options as $option)
            <li class="list-group-item">
                @if($urlCallback ?? false)
                    <a href="{{ $urlCallback($option->getValue()) }}">{{ $option->getLabel() }}</a>
                @else
                    {{ $option->getLabel() }}
                @endif
            </li>
        @endforeach
    </ul>
@endif