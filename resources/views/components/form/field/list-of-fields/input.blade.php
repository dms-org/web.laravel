<?php /** @var \Dms\Web\Laravel\Renderer\Form\IFieldRenderer $fieldRenderer */ ?>
<?php /** @var \Dms\Core\Form\IField $elementField */ ?>
<?php $elementField = $elementField->withName($name . '[]', $label); ?>
<ul
        class="list-group list-fields"
        @if($exactElements !== null)
        data-min-elements="{{ $exactElements }}"
        data-max-elements="{{ $exactElements }}"
        @else
        @if($minElements !== null) data-min-elements="{{ $minElements }}" @endif
        @if($maxElements !== null) data-max-elements="{{ $maxElements }}" @endif
        @endif
>
    <li class="list-group-item hidden list-field-template">
        <div class="list-field-input">{{ $fieldRenderer->render($elementField->withInitialValue(null)) }}</div>
        <button class="btn btn-danger btn-remove-field"><span class="fa fa-cross"></span></button>
    </li>

    @if ($value !== null)
        @foreach ($value as $valueElement)
            <li class="list-group-item list-field-item">
                <div class="list-field-input">{{ $fieldRenderer->render($elementField->withInitialValue($valueElement)) }}</div>
                <button class="btn btn-danger btn-remove-field"><span class="fa fa-cross"></span></button>
            </li>
        @endforeach
    @endif

    <li class="list-group-item list-field-add">
        <button class="btn btn-success btn-add-field">Add <span class="fa fa-plus"></span></button>
    </li>
</ul>