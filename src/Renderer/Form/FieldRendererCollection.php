<?php declare(strict_types=1);

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
     * FieldRendererCollection constructor.
     *
     * @param IFieldRenderer[] $fieldRenderers
     */
    public function __construct(array $fieldRenderers)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'fieldRenderers', $fieldRenderers,
            IFieldRenderer::class);

        foreach ($fieldRenderers as $fieldRenderer) {
            $this->fieldRenderers[$fieldRenderer->getFieldTypeClass()][] = $fieldRenderer;
            $fieldRenderer->setRendererCollection($this);
        }
    }

    /**
     * @param IField $field
     *
     * @return IFieldRenderer
     * @throws UnrenderableFieldException
     */
    public function findRendererFor(IField $field) : IFieldRenderer
    {
        $fieldType = $field->getType();

        $fieldTypeClass = get_class($fieldType);

        while ($fieldTypeClass) {

            if (isset($this->fieldRenderers[$fieldTypeClass])) {
                foreach ($this->fieldRenderers[$fieldTypeClass] as $fieldRenderer) {
                    if ($fieldRenderer->accepts($field)) {
                        return $fieldRenderer;
                    }
                }
            }

            $fieldTypeClass = get_parent_class($fieldTypeClass);
        }

        throw UnrenderableFieldException::format(
            'Could not render field \'%s\' with field type of class %s: no matching field renderer could be found',
            $field->getName(), get_class($fieldType)
        );
    }
}