<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package\Module;

use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\ICms;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\ITableView;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\ITableStructure;
use Dms\Web\Laravel\Error\DmsError;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;

/**
 * The table controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableController extends DmsController
{
    /**
     * @var TableRenderer
     */
    protected $tableRenderer;

    /**
     * TableController constructor.
     *
     * @param ICms          $cms
     * @param TableRenderer $tableRenderer
     */
    public function __construct(
        ICms $cms,
        TableRenderer $tableRenderer
    )
    {
        parent::__construct($cms);
        $this->tableRenderer = $tableRenderer;
    }

    public function showTable(ModuleContext $moduleContext, string $tableName, string $viewName)
    {
        $module = $moduleContext->getModule();

        $table = $this->loadTable($module, $tableName);

        if ($table instanceof ISummaryTable) {
            return redirect()
                ->to($moduleContext->getUrl('dashboard'))
                ->with('initial-view-name', $viewName);
        }

        $this->loadTableView($table, $viewName);

        return view('dms::package.module.table')
            ->with([
                'assetGroups'     => ['tables'],
                'pageTitle'       => implode(' :: ', array_merge($moduleContext->getTitles(), [StringHumanizer::title($tableName)])),
                'pageSubTitle'    => $viewName,
                'breadcrumbs'     => $moduleContext->getBreadcrumbs(),
                'finalBreadcrumb' => StringHumanizer::title($tableName),
                'tableContent'    => $this->tableRenderer->renderTableControl($moduleContext, $table, $viewName),
            ]);
    }

    public function loadTableRows(Request $request, ModuleContext $moduleContext, string $tableName, string $viewName)
    {
        $table = $this->loadTable($moduleContext->getModule(), $tableName);

        $tableView = $this->loadTableView($table, $viewName);

        $criteria = $tableView->getCriteriaCopy() ?: $table->getDataSource()->criteria()->loadAll();

        $isFiltered = $this->filterCriteriaFromRequest($request, $table->getDataSource()->getStructure(), $criteria);

        return $this->tableRenderer->renderTableData(
            $moduleContext,
            $table,
            $table->getDataSource()->load($criteria),
            $viewName,
            $isFiltered
        );
    }

    protected function filterCriteriaFromRequest(Request $request, ITableStructure $structure, RowCriteria $criteria) : bool
    {
        $validComponentIds = [];

        foreach ($structure->getColumns() as $column) {
            foreach ($column->getComponents() as $component) {
                $validComponentIds[] = $column->getName() . '.' . $component->getName();
            }
        }

        $this->validate($request, [
            'offset'                 => 'integer|min:0',
            'amount'                 => 'integer|min:0',
            'condition_mode'         => 'required|in:or,and',
            'conditions.*.component' => 'required|in:' . implode(',', $validComponentIds),
            'conditions.*.operator'  => 'required|in:' . implode(',', ConditionOperator::getAll()),
            'conditions.*.value'     => 'required',
            'orderings.*.component'  => 'required|in:' . implode(',', $validComponentIds),
            'orderings.*.direction'  => 'required|in:' . implode(',', OrderingDirection::getAll()),
        ]);

        if ($request->has('offset')) {
            $criteria->skipRows((int)$request->input('offset') + $criteria->getRowsToSkip());
        }

        if ($request->has('max_rows')) {
            $criteria->maxRows(min((int)$request->input('max_rows'), $criteria->getAmountOfRows() ?: PHP_INT_MAX));
        }

        $isFiltered = false;

        if ($request->has('conditions')) {
            $isFiltered = true;

            $criteria->setConditionMode($request->input('condition_mode'));

            foreach ($request->input('conditions') as $condition) {
                $criteria->where($condition['component'], $condition['operator'], $condition['value']);
            }
        }

        if ($request->has('orderings')) {
            $isFiltered = true;

            $criteria->clearOrderings();
            foreach ($request->input('orderings') as $ordering) {
                $criteria->orderBy($ordering['component'], $ordering['direction']);
            }
        }

        return $isFiltered;
    }

    /**
     * @param ITableDisplay $table
     * @param string        $viewName
     *
     * @return ITableView
     */
    protected function loadTableView(ITableDisplay $table, string $viewName) : ITableView
    {
        try {
            return $table->getView($viewName);
        } catch (InvalidArgumentException $e) {
            DmsError::abort(404);
        }
    }

    /**
     * @param IModule $module
     * @param string  $tableName
     *
     * @return array|ITableDisplay
     */
    protected function loadTable(IModule $module, string $tableName) : ITableDisplay
    {
        try {
            return $module->getTable($tableName);
        } catch (InvalidArgumentException $e) {
            $response = response()->json([
                'message' => 'Invalid table name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }
}