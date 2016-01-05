<?php

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;

/**
 * The field renderer collection.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldRendererCollection
{
    /**
     * @var IFieldRenderer[][]
     */
    protected $fieldRenderers;

    /**
     * AggregateFieldRenderer constructor.
     *
     * @param IFieldRenderer[] $fieldRenderers
     */
    public function __construct(array $fieldRenderers)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fieldRenderers', $fieldRenderers, IFieldRenderer::class);

        foreach ($fieldRenderers as $fieldRenderer) {
            $this->fieldRenderers[$fieldRenderer->getFieldTypeClass()][] = $fieldRenderer;
            $fieldRenderer->setRendererCollection($this);
        }
    }

    /**
     * Renders the supplied field as a html string.
     *
     * @param IField $field
     *
     * @return string
     * @throws UnrenderableFieldException
     */
    public function render(IField $field)
    {
        return $this->findRendererFor($field)->render($field);
    }

    /**
     * @param IField $field
     *
     * @return IFieldRenderer
     * @throws UnrenderableFieldException
     */
    public function findRendererFor(IField $field)
    {
        $fieldType = $field->getType();

        $fieldTypeClasses = array_merge([get_class($fieldType)], class_parents($fieldType));

        foreach ($fieldTypeClasses as $class) {
            if (isset($this->fieldRenderers[$class])) {
                foreach ($this->fieldRenderers[$class] as $fieldRenderer) {
                    if ($fieldRenderer->accepts($field)) {
                        return $fieldRenderer;
                    }
                }
            }
        }

        throw UnrenderableFieldException::format(
                'Could not render field \'%s\' with field type of class %s: no matching field renderer could be found',
                $field->getName(), get_class($fieldType)
        );
    }
}