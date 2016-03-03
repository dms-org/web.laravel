<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\ITableStructure;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Action\ObjectActionButtonBuilder;

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
     * @var ObjectActionButtonBuilder
     */
    private $actionButtonBuilder;

    /**
     * TableRenderer constructor.
     *
     * @param ColumnRendererFactoryCollection $columnRendererFactories
     * @param ObjectActionButtonBuilder       $actionButtonBuilder
     */
    public function __construct(ColumnRendererFactoryCollection $columnRendererFactories, ObjectActionButtonBuilder $actionButtonBuilder)
    {
        $this->columnRendererFactories = $columnRendererFactories;
        $this->actionButtonBuilder     = $actionButtonBuilder;
    }

    /**
     * Renders the supplied data table as a html string.
     *
     * @param ModuleContext $moduleContext
     * @param ITableDisplay $table
     * @param IDataTable    $tableData
     * @param string        $viewName
     * @param bool          $isFiltered
     *
     * @return string
     * @throws UnrenderableColumnComponentException
     */
    public function renderTableData(ModuleContext $moduleContext, ITableDisplay $table, IDataTable $tableData, string $viewName = null, bool $isFiltered = false) : string
    {
        $columnRenderers = [];

        foreach ($tableData->getStructure()->getColumns() as $column) {
            $columnRenderers[$column->getName()] = $this->columnRendererFactories->buildRendererFor($column);
        }

        $rowActionButtons = [];
        if ($moduleContext->getModule() instanceof IReadModule && $table instanceof ISummaryTable) {
            foreach ($this->actionButtonBuilder->buildActionButtons($moduleContext) as $actionButton) {
                $rowActionButtons[$actionButton->getName()] = $actionButton;
            }
        }

        return view('dms::components.table.data-table')
            ->with([
                'columns'          => $tableData->getStructure()->getColumns(),
                'columnRenderers'  => $columnRenderers,
                'sections'         => $tableData->getSections(),
                'rowActionButtons' => $rowActionButtons,
                'allowsReorder'    => !$isFiltered && $viewName && $this->allowsRowReorder($table, $viewName),
            ])
            ->render();
    }

    /**
     * Renders the supplied table control as a html string.
     *
     * @param ModuleContext $moduleContext
     * @param ITableDisplay $table
     * @param string        $viewName
     *
     * @return string
     */
    public function renderTableControl(ModuleContext $moduleContext, ITableDisplay $table, string $viewName = null) : string
    {
        $viewName = $viewName ?? $table->getDefaultView()->getName();
        $columns  = $table->getDataSource()->getStructure()->getColumns();

        if ($moduleContext->getModule() instanceof IReadModule && $table instanceof ISummaryTable) {
            unset($columns[IReadModule::SUMMARY_TABLE_ID_COLUMN]);
        }

        if ($this->allowsRowReorder($table, $viewName)) {
            $reorderRowActionUrl = $moduleContext->getUrl('action.run', [$table->getReorderAction($viewName)->getName()]);
        } else {
            $reorderRowActionUrl = null;
        }

        return view('dms::components.table.table-control')
            ->with([
                'columns'                      => $columns,
                'table'                        => $table->getView($viewName),
                'loadRowsUrl'                  => $moduleContext->getUrl('table.view.load', [$table->getName(), $viewName]),
                'reorderRowActionUrl'          => $reorderRowActionUrl,
                'stringFilterableComponentIds' => $this->getStringFilterableColumnComponentIds($table->getDataSource()->getStructure()),
            ])
            ->render();
    }

    protected function getStringFilterableColumnComponentIds(ITableStructure $structure) : array
    {
        $componentIds = [];

        foreach ($structure->getColumns() as $column) {
            foreach ($column->getComponents() as $component) {
                if ($component->getType()->hasOperator(ConditionOperator::STRING_CONTAINS_CASE_INSENSITIVE)) {
                    $componentIds[] = $column->getName() . '.' . $component->getName();
                }
            }
        }

        return $componentIds;
    }

    /**
     * @param ITableDisplay $table
     * @param string        $viewName
     *
     * @return bool
     */
    protected function allowsRowReorder(ITableDisplay $table, string $viewName) : bool
    {
        return $table instanceof ISummaryTable
        && $table->hasReorderAction($viewName)
        && $table->getReorderAction($viewName)->isAuthorized();
    }
}