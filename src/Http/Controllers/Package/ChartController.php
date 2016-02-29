<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\ICms;
use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Model\Criteria\OrderingDirection;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Module\IChartView;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Core\Table\Chart\Criteria\ChartCriteria;
use Dms\Core\Table\Chart\IChartStructure;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Chart\ChartControlRenderer;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;

/**
 * The chart controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartController extends DmsController
{
    /**
     * @var ChartControlRenderer
     */
    protected $chartRenderer;

    /**
     * ChartController constructor.
     *
     * @param ICms                 $cms
     * @param ChartControlRenderer $chartRenderer
     */
    public function __construct(
        ICms $cms,
        ChartControlRenderer $chartRenderer
    )
    {
        parent::__construct($cms);
        $this->chartRenderer = $chartRenderer;
    }

    public function showChart(string $packageName, string $moduleName, string $chartName, string $viewName)
    {
        $chart = $this->loadChart($packageName, $moduleName, $chartName);

        $this->loadChartView($chart, $viewName);

        return view('dms::package.module.chart')
            ->with([
                'assetGroups'     => ['charts'],
                'pageTitle'       => StringHumanizer::title($packageName . ' :: ' . $moduleName . ' :: ' . $chartName),
                'pageSubTitle'    => $viewName,
                'breadcrumbs'     => [
                    route('dms::index')                                                 => 'Home',
                    route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                    route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
                ],
                'finalBreadcrumb' => StringHumanizer::title($chartName),
                'chartRenderer'   => $this->chartRenderer,
                'packageName'     => $packageName,
                'moduleName'      => $moduleName,
                'chart'           => $chart,
                'viewName'        => $viewName,
            ]);
    }

    public function loadChartData(Request $request, string $packageName, string $moduleName, string $chartName, string $viewName)
    {
        $chart = $this->loadChart($packageName, $moduleName, $chartName);

        $chartView = $this->loadChartView($chart, $viewName);

        $criteria = $chartView->getCriteriaCopy() ?: $chart->getDataSource()->criteria();

        $this->filterCriteriaFromRequest($request, $chart->getDataSource()->getStructure(), $criteria);

        return $this->chartRenderer->renderChart(
            $chart->getDataSource()->load($criteria)
        );
    }

    protected function filterCriteriaFromRequest(Request $request, IChartStructure $structure, ChartCriteria $criteria)
    {
        $axisNames = [];

        foreach ($structure->getAxes() as $axis) {
            $axisNames[] = $axis->getName();
        }

        $this->validate($request, [
            'conditions.*.axis'     => 'required|in:' . implode(',', $axisNames),
            'conditions.*.operator' => 'required|in:' . implode(',', ConditionOperator::getAll()),
            'conditions.*.value'    => 'required',
            'orderings.*.component' => 'required|in:' . implode(',', $axisNames),
            'orderings.*.direction' => 'required|in' . implode(',', OrderingDirection::getAll()),
        ]);


        if ($request->has('conditions')) {
            foreach ($request->input('conditions') as $condition) {
                $criteria->where($condition['axis'], $condition['operator'], $condition['value']);
            }
        }

        if ($request->has('orderings')) {
            foreach ($request->input('orderings') as $ordering) {
                $criteria->orderBy($ordering['component'], $ordering['direction']);
            }
        }
    }

    /**
     * @param IChartDisplay $chart
     * @param string        $chartView
     *
     * @return IChartView
     */
    protected function loadChartView(IChartDisplay $chart, string $chartView) : IChartView
    {
        try {
            return $chart->hasView($chartView) ? $chart->getView($chartView) : $chart->getDefaultView();
        } catch (InvalidArgumentException $e) {
            abort(404);
        }
    }

    /**
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return IChartDisplay
     */
    protected function loadChart(string $packageName, string $moduleName, string $actionName) : IChartDisplay
    {
        try {
            $action = $this->cms
                ->loadPackage($packageName)
                ->loadModule($moduleName)
                ->getChart($actionName);

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
                'message' => 'Invalid chart name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }
}