<?php

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Widget\IWidget;
use Dms\Core\Widget\TableWidget;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;

/**
 * The widget renderer for data tables
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableWidgetRenderer extends WidgetRenderer
{
    /**
     * @var TableRenderer
     */
    protected $tableRenderer;

    /**
     * TableWidgetRenderer constructor.
     *
     * @param TableRenderer $tableRenderer
     */
    public function __construct(TableRenderer $tableRenderer)
    {
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * Returns whether this renderer can render the supplied widget.
     *
     * @param IWidget $widget
     *
     * @return bool
     */
    public function accepts(IWidget $widget)
    {
        return $widget instanceof TableWidget;
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IWidget $widget)
    {
        /** @var TableWidget $widget */
        return (string)view('dms::components.widget.data-table')
            ->with([
                'dataTableContent' => $this->tableRenderer->renderTable($widget->loadData()),
            ]);
    }
}