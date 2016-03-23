<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldOptions;
use Dms\Core\Form\IFieldType;
use Dms\Core\Util\Hashing\ValueHasher;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;

/**
 * The options field renderer base class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class OptionsFieldRender extends BladeFieldRenderer
{
    protected function renderFieldValue(
        FormRenderingContext $renderingContext,
        IField $field,
        $value,
        IFieldType $fieldType
    ) : string
    {
        /** @var IFieldOptions $options */
        $options     = $fieldType->get(FieldType::ATTR_OPTIONS);
        $urlCallback = RelatedEntityLinker::getUrlCallbackFor($options);

        $label = null;
        $url   = null;

        foreach ($options->getAll() as $option) {
            if (ValueHasher::areEqual($value, $option->getValue())) {
                $label = $option->getLabel();
                $url   = $urlCallback ? $urlCallback($option->getValue()) : null;
            }
        }

        return $this->renderValueViewWithNullDefault(
            $field, $label,
            'dms::components.field.string.value',
            ['url' => $url]
        );
    }
}