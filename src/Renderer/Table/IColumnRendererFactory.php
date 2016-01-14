<?php

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\IColumn;

/**
 * The column renderer factory interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IColumnRendererFactory
{
    /**
     * Returns whether this factory supports the supplied column
     *
     * @param IColumn $column
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function accepts(IColumn $column);

    /**
     * Builds a column renderer for the supplied column.
     *
     * @param IColumn                           $column
     * @param ColumnComponentRendererCollection $componentRenderers
     *
     * @return IColumnRenderer
     * @throws InvalidArgumentException
     */
    public function buildRenderer(IColumn $column, ColumnComponentRendererCollection $componentRenderers);
}