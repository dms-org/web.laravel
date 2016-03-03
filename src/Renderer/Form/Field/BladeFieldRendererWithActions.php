<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Core\Module\IModule;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;
use Dms\Web\Laravel\Renderer\Form\IFieldRendererWithActions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The blade field renderer with actions
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class BladeFieldRendererWithActions extends BladeFieldRenderer implements IFieldRendererWithActions
{
    /**
     * @param FormRenderingContext $renderingContext
     * @param IField               $field
     * @param Request              $request
     * @param string               $actionName
     * @param array                $data
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    final public function handleAction(FormRenderingContext $renderingContext, IField $field, Request $request, string $actionName = null, array $data)
    {
        if (!$this->accepts($renderingContext, $field)) {
            throw InvalidArgumentException::format(
                'Field \'%s\' cannot be rendered in renderer of type %s',
                $field->getName(), get_class($this)
            );
        }

        return $this->handleFieldAction($renderingContext, $field, $field->getType(), $request, $actionName, $data);
    }

    /**
     * @param FormRenderingContext $renderingContext
     * @param IField               $field
     * @param IFieldType           $fieldType
     * @param Request              $request
     * @param string               $actionName
     * @param array                $data
     *
     * @return Response
     */
    abstract protected function handleFieldAction(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType, Request $request, string $actionName = null, array $data);
}