<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Table\IDataTable;
use Dms\Core\Table\ITableRow;
use Dms\Core\Table\ITableStructure;
use Dms\Web\Laravel\Renderer\Table\RowAction\RowActionButton;
use Dms\Web\Laravel\Util\StringHumanizer;

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
     * @param IModule       $module
     * @param ITableDisplay $table
     * @param IDataTable    $tableData
     *
     * @return string
     * @throws UnrenderableColumnComponentException
     */
    public function renderTableData(IModule $module, ITableDisplay $table, IDataTable $tableData) : string
    {
        $columnRenderers = [];

        foreach ($tableData->getStructure()->getColumns() as $column) {
            $columnRenderers[$column->getName()] = $this->columnRendererFactories->buildRendererFor($column);
        }


        if ($module instanceof IReadModule && $table instanceof ISummaryTable) {
            $rowActionButtons = $this->buildRowActions($module);
        } else {
            $rowActionButtons = [];
        }

        return view('dms::components.table.data-table')
            ->with([
                'columns'          => $tableData->getStructure()->getColumns(),
                'columnRenderers'  => $columnRenderers,
                'sections'         => $tableData->getSections(),
                'rowActionButtons' => $rowActionButtons,
            ])
            ->render();
    }

    /**
     * @param IReadModule $module
     *
     * @return callable[]
     */
    protected function buildRowActions(IReadModule $module) : array
    {
        $rowActions = [];

        foreach ($module->getObjectActions() as $action) {
            $requiresExtraFormSubmission = $action->getStagedForm()->getAmountOfStages() > 1;

            if ($requiresExtraFormSubmission) {
                $formUrl = route('dms::package.module.action.form', [$module->getPackageName(), $module->getName(), $action->getName(), '__object__']);
            } else {
                $formUrl = route('dms::package.module.action.run', [$module->getPackageName(), $module->getName(), $action->getName(),
                    IObjectAction::OBJECT_FIELD_NAME => '__object__'
                ]);
            }

            $rowActions[$action->getName()] = new RowActionButton(
                !$requiresExtraFormSubmission,
                $action->getName(),
                StringHumanizer::humanize($action->getName()),
                function (ITableRow $row) use ($formUrl) {
                    $objectId = $row->getCellComponentData(IReadModule::SUMMARY_TABLE_ID_COLUMN);

                    return str_replace('__object__', $objectId, $formUrl);
                }
            );
        }

        return $rowActions;
    }

    /**
     * Renders the supplied table control as a html string.
     *
     * @param IModule       $module
     * @param ITableDisplay $table
     * @param string        $viewName
     *
     * @return string
     */
    public function renderTableControl(IModule $module, ITableDisplay $table, string $viewName) : string
    {
        $columns = $table->getDataSource()->getStructure()->getColumns();

        if ($module instanceof IReadModule && $table instanceof ISummaryTable) {
            unset($columns[IReadModule::SUMMARY_TABLE_ID_COLUMN]);
        }

        if ($table instanceof ISummaryTable
            && $table->hasReorderAction($viewName)
            && $table->getReorderAction($table)->isAuthorized()
        ) {
            $reorderRowActionUrl = route(
                'dms::package.module.action',
                [$module->getPackageName(), $module->getName(), $table->getReorderAction($viewName)->getName()]
            );
        } else {
            $reorderRowActionUrl = null;
        }

        return view('dms::components.table.table-control')
            ->with([
                'columns'                      => $columns,
                'table'                        => $table->getView($viewName),
                'loadRowsUrl'                  => route(
                    'dms::package.module.table.view.load',
                    [$module->getPackageName(), $module->getName(), $table->getName(), $viewName]
                ),
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
}