<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Module\IModule;
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
    public function accepts(IModule $module, IWidget $widget) : bool
    {
        return $widget instanceof TableWidget;
    }

    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return array
     */
    protected function getWidgetLinks(IModule $module, IWidget $widget) : array
    {
        /** @var TableWidget $widget */
        $tableDisplay = $widget->getTableDisplay();

        $links = [];

        foreach ($tableDisplay->getViews() as $tableView) {
            $viewParams = [$module->getPackageName(), $module->getName(), $tableDisplay->getName(), $tableView->getName()];

            $links[route('dms::package.module.table.view.show', $viewParams)] = $tableView->getLabel();
        }

        return $links;
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IModule $module, IWidget $widget) : string
    {
        /** @var TableWidget $widget */
        $tableDisplay = $widget->getTableDisplay();

        return view('dms::components.widget.data-table')
            ->with([
                'dataTableContent' => $this->tableRenderer->renderTableData($module, $tableDisplay, $widget->loadData()),
            ])
            ->render();
    }
}