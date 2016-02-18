<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\IDataTable;

/**
 * The table renderer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRenderer
{
    /**
     * @var ColumnRendererFactoryCollection
     */
    protected $columnRendererFactories;

    /**
     * TableRenderer constructor.
     *
     * @param ColumnRendererFactoryCollection $columnRendererFactories
     */
    public function __construct(ColumnRendererFactoryCollection $columnRendererFactories)
    {
        $this->columnRendererFactories = $columnRendererFactories;
    }

    /**
     * Renders the supplied data table as a html string.
     *
     * @param IDataTable $table
     *
     * @return string
     */
    public function renderTableData(IDataTable $table) : string
    {
        $columnRenderers = [];

        foreach ($table->getStructure()->getColumns() as $column) {
            $columnRenderers[$column->getName()] = $this->columnRendererFactories->buildRendererFor($column);
        }

        return view('dms::components.table.data-table')
            ->with([
                'columns'         => $table->getStructure()->getColumns(),
                'columnRenderers' => $columnRenderers,
                'sections'        => $table->getSections(),
            ])
            ->render();
    }

    /**
     * Renders the supplied table control as a html string.
     *
     * @param string        $packageName
     * @param string        $moduleName
     * @param ITableDisplay $table
     * @param string        $viewName
     *
     * @return string
     */
    public function renderTableControl(string $packageName, string $moduleName, ITableDisplay $table, string $viewName) : string
    {
        if ($table instanceof ISummaryTable
            && $table->hasReorderAction($viewName)
            && $table->getReorderAction($table)->isAuthorized()
        ) {
            $reorderRowActionUrl = route(
                'dms::package.module.action',
                [$packageName, $moduleName, $table->getReorderAction($viewName)->getName()]
            );
        } else {
            $reorderRowActionUrl = null;
        }

        return view('dms::components.table.table-control')
            ->with([
                'columns'             => $table->getDataSource()->getStructure()->getColumns(),
                'structure'           => $table->getDataSource()->getStructure(),
                'table'               => $table->getView($viewName),
                'loadRowsUrl'         => route(
                    'dms::package.module.table.view.load',
                    [$packageName, $moduleName, $table->getName(), $viewName]
                ),
                'reorderRowActionUrl' => $reorderRowActionUrl,
            ])
            ->render();
    }
}