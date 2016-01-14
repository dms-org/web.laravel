<?php

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IColumnComponent;
use Dms\Web\Laravel\Renderer\Form\IFieldRenderer;
use Dms\Web\Laravel\Renderer\Table\Column\Component\FieldComponentRenderer;

/**
 * The column component renderer collection.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ColumnComponentRendererCollection
{
    /**
     * @var IColumnComponentRenderer[]
     */
    protected $columnComponentRenderers;

    /**
     * ColumnComponentRendererCollection constructor.
     *
     * @param IColumnComponentRenderer[]|IFieldRenderer[] $columnComponentRenderers
     */
    public function __construct(array $columnComponentRenderers)
    {
        foreach ($columnComponentRenderers as $key => $renderer) {
            if ($renderer instanceof IFieldRenderer) {
                $columnComponentRenderers[$key] = new FieldComponentRenderer($renderer);
            }
        }

        InvalidArgumentException::verifyAllInstanceOf(
                __METHOD__, 'columnComponentRenderers', $columnComponentRenderers, IColumnComponentRenderer::class
        );

        $this->columnComponentRenderers = $columnComponentRenderers;
    }

    /**
     * @param IColumnComponent $component
     *
     * @return IColumnComponentRenderer
     * @throws UnrenderableColumnComponentException
     */
    public function findRendererFor(IColumnComponent $component)
    {
        foreach ($this->columnComponentRenderers as $renderer) {
            if ($renderer->accepts($component)) {
                return $renderer;
            }
        }

        throw UnrenderableColumnComponentException::format(
                'Could not render column component \'%s\' with value type %s: no matching renderer could be found',
                $component->getName(), get_class($component->getType()->getPhpType()->asTypeString())
        );
    }
}