<?php /** @var \Dms\Web\Laravel\Renderer\Form\FormRenderingContext $renderingContext */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Form\IFieldRenderer $fieldRenderer */ ?>
<?php /** @var \Dms\Core\Form\IField $elementField */ ?>
<?php $elementField = $elementField->withName($name . '[]', $label); ?>
<ul class="dms-display-list list-group">
    @foreach ($value as $valueElement)
        <li class="list-group-item">{!! $fieldRenderer->renderValue($renderingContext, $elementField->withInitialValue($valueElement)) !!}</li>
    @endforeach
</ul>