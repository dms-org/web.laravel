<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Common\Crud\Table\ISummaryTable;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\ICms;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\ITableView;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\ITableStructure;
use Dms\Web\Laravel\Http\Controllers\DmsController;
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

    public function showTable(string $packageName, string $moduleName, string $tableName, string $viewName)
    {
        /** @var IModule $module */
        /** @var ITableDisplay $table */
        list($module, $table) = $this->loadTable($packageName, $moduleName, $tableName);


        if ($table instanceof ISummaryTable) {
            return redirect()->route('dms::package.module.dashboard', [$packageName, $moduleName]);
        }

        $this->loadTableView($table, $viewName);

        return view('dms::package.module.table')
            ->with([
                'pageTitle'       => StringHumanizer::title($packageName . ' :: ' . $moduleName . ' :: ' . $tableName),
                'pageSubTitle'    => $viewName,
                'breadcrumbs'     => [
                    route('dms::index')                                                 => 'Home',
                    route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                    route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
                ],
                'finalBreadcrumb' => StringHumanizer::title($tableName),
                'tableRenderer'   => $this->tableRenderer,
                'module'          => $module,
                'table'           => $table,
                'viewName'        => $viewName,
            ]);
    }

    public function loadTableRows(Request $request, string $packageName, string $moduleName, string $tableName, string $viewName)
    {
        /** @var IModule $module */
        /** @var ITableDisplay $table */
        list($module, $table) = $this->loadTable($packageName, $moduleName, $tableName);

        $tableView = $this->loadTableView($table, $viewName);

        $criteria = $tableView->getCriteriaCopy() ?: $table->getDataSource()->criteria();

        $isFiltered = $this->filterCriteriaFromRequest($request, $table->getDataSource()->getStructure(), $criteria);

        return $this->tableRenderer->renderTableData(
            $module,
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
            $tableView = $table->getView($viewName);
            return $tableView;
        } catch (InvalidArgumentException $e) {
            abort(404);
        }
    }

    /**
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return array
     */
    protected function loadTable(string $packageName, string $moduleName, string $actionName) : array
    {
        try {
            $module = $this->cms->loadPackage($packageName)->loadModule($moduleName);
            $table  = $module->getTable($actionName);

            return [$module, $table];
        } catch (PackageNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid package name',
            ], 404);
        } catch (ModuleNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid module name',
            ], 404);
        } catch (InvalidArgumentException $e) {
            $response = response()->json([
                'message' => 'Invalid table name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }
}