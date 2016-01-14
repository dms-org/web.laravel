<?php

namespace Dms\Web\Laravel\Renderer\Table\Column;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IColumn;
use Dms\Web\Laravel\Renderer\Table\IColumnComponentRenderer;

/**
 * The default column renderer and factory
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultColumnRendererAndFactory extends ColumnRendererAndFactory
{
    /**
     * Returns whether this factory supports the supplied column
     *
     * @param IColumn $column
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function accepts(IColumn $column)
    {
        return true;
    }

    /**
     * Renders the column header as a html string.
     *
     * @param IColumn $column
     *
     * @return string
     */
    protected function renderHeader(IColumn $column)
    {
        $componentLabels = [];

        foreach ($column->getComponents() as $component) {
            $componentLabels[] = e($component->getLabel());
        }

        return $this->wrapInNestedSubTags($componentLabels);
    }

    /**
     * Renders the column value as a html string.
     *
     * @param IColumn                    $column
     * @param IColumnComponentRenderer[] $componentRenderers
     * @param array                      $value
     *
     * @return string
     */
    protected function renderValue(IColumn $column, array $componentRenderers, array $value)
    {
        if (\count($value) === 1) {
            return \reset($componentRenderers)->render(\reset($value));
        }

        $renderedComponents = [];
        foreach ($column->getComponents() as $componentName => $component) {
            $renderedComponents[] = $componentRenderers[$componentName]->render($component, $value[$componentName]);
        }

        return $this->wrapInNestedSubTags($renderedComponents);
    }

    protected function wrapInNestedSubTags(array $elements)
    {
        $html = '';

        $firstElement = array_shift($elements);
        $html .= $firstElement;

        foreach ($elements as $element) {
            $html .= '<br><sup>' . $element;
        }

        foreach ($elements as $element) {
            $html .= '</sup>';
        }

        return $html;
    }
}