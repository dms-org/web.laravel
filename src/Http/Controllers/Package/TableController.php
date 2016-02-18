<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\ICms;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Module\ITableDisplay;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Table\Criteria\RowCriteria;
use Dms\Core\Table\ITableStructure;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
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
    ) {
        parent::__construct($cms);
        $this->tableRenderer = $tableRenderer;
    }

    public function showTable($packageName, $moduleName, $tableName, $viewName)
    {
        $table = $this->loadTable($packageName, $moduleName, $tableName);

        return view('dms::package.module.table')
            ->with([
                'pageTitle'       => ucwords($packageName . ' > ' . $moduleName . ' > ' . $tableName),
                'pageSubTitle'    => $viewName,
                'breadcrumbs'     => [
                    route('dms::index')                                 => 'Home',
                    route('dms::package.dashboard', $packageName)       => ucwords($packageName),
                    route('dms::package.module.dashboard', $moduleName) => $moduleName,
                ],
                'finalBreadcrumb' => ucwords($tableName),
                'tableRenderer'   => $this->tableRenderer,
                'packageName'     => $packageName,
                'moduleName'      => $moduleName,
                'table'           => $table,
                'viewName'        => $viewName,
            ]);
    }

    public function loadTableRows(Request $request, $packageName, $moduleName, $tableName, $tableView)
    {
        $table = $this->loadTable($packageName, $moduleName, $tableName);

        $criteria = $table->getView($tableView)->getCriteriaCopy() ?: $table->getDataSource()->criteria();

        $this->filterCriteriaFromRequest($request, $table->getDataSource()->getStructure(), $criteria);

        return $this->tableRenderer->renderTableData(
            $table->getDataSource()->load($criteria)
        );
    }

    protected function filterCriteriaFromRequest(Request $request, ITableStructure $structure, RowCriteria $criteria)
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
            'conditions.*.component' => 'required|in:' . implode(',', $validComponentIds),
            'conditions.*.operator'  => 'required|in:' . implode(',', ConditionOperator::getAll()),
            'conditions.*.value'     => 'required',
            'orderings.*.component'  => 'required|in:' . implode(',', $validComponentIds),
            'orderings.*.direction'  => 'required|in' . implode(',', OrderingDirection::getAll()),
        ]);

        if ($request->has('offset')) {
            $criteria->skipRows($request->input('offset') + $criteria->getRowsToSkip());
        }

        if ($request->has('amount')) {
            $criteria->maxRows(min($request->input('amount'), $criteria->getAmountOfRows() ?: PHP_INT_MAX));
        }

        if ($request->has('conditions')) {
            foreach ($request->input('conditions') as $condition) {
                $criteria->where($condition['component'], $condition['operator'], $condition['value']);
            }
        }

        if ($request->has('orderings')) {
            foreach ($request->input('orderings') as $ordering) {
                $criteria->orderBy($ordering['component'], $ordering['direction']);
            }
        }
    }

    /**
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return ITableDisplay
     */
    protected function loadTable(string $packageName, string $moduleName, string $actionName) : \Dms\Core\Module\ITableDisplay
    {
        try {
            $action = $this->cms
                ->loadPackage($packageName)
                ->loadModule($moduleName)
                ->getTable($actionName);

            return $action;
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