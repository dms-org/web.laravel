<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\IFieldRendererWithActions;
use Illuminate\Http\Response;

/**
 * The blade field renderer with actions
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class BladeFieldRendererWithActions extends BladeFieldRenderer implements IFieldRendererWithActions
{
    /**
     * @param IField $field
     * @param string $actionName
     * @param array  $data
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    final public function handleAction(IField $field, string $actionName, array $data) : Response
    {
        if (!$this->accepts($field)) {
            throw InvalidArgumentException::format(
                'Field \'%s\' cannot be rendered in renderer of type %s',
                $field->getName(), get_class($this)
            );
        }

        return $this->handleFieldAction($field, $field->getType(), $actionName, $data);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     * @param string     $actionName
     * @param array      $data
     *
     * @return Response
     */
    abstract protected function handleFieldAction(IField $field, IFieldType $fieldType, string $actionName, array $data) : Response;
}