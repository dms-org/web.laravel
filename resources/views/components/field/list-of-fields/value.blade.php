<?php /** @var \Dms\Web\Laravel\Renderer\Form\IFieldRenderer $fieldRenderer */ ?>
<?php /** @var \Dms\Core\Form\IField $elementField */ ?>
<?php $elementField = $elementField->withName($name . '[]', $label); ?>
<ul class="dms-display-list">
    @foreach ($value as $valueElement)
        <li class="list-group-item">{{ $fieldRenderer->renderValue($elementField->withInitialValue($valueElement)) }}</li>
    @endforeach
</ul>