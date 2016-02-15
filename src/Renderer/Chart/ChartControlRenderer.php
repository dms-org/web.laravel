<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Core\Module\IChartDisplay;
use Dms\Core\Table\Chart\IChartDataTable;

/**
 * The chart control renderer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartControlRenderer
{
    /**
     * @var ChartRendererCollection
     */
    protected $chartRenderers;

    /**
     * ChartControlRenderer constructor.
     *
     * @param ChartRendererCollection $chartRenderers
     */
    public function __construct(ChartRendererCollection $chartRenderers)
    {
        $this->chartRenderers = $chartRenderers;
    }

    /**
     * Renders the supplied chart control as a html string.
     *
     * @param IChartDataTable $chartDataTable
     *
     * @return string
     * @throws UnrenderableChartException
     */
    public function renderChart(IChartDataTable $chartDataTable) : string
    {
        return $this->chartRenderers->findRendererFor($chartDataTable)->render($chartDataTable);
    }

    /**
     * Renders the supplied chart control as a html string.
     *
     * @param string        $packageName
     * @param string        $moduleName
     * @param IChartDisplay $chart
     * @param string        $viewName
     *
     * @return string
     */
    public function renderChartControl(string $packageName, string $moduleName, IChartDisplay $chart, string $viewName) : string
    {
        return (string)view('dms::components.table.chart-control')
            ->with([
                'structure'    => $chart->getDataSource()->getStructure(),
                'table'        => $chart->getView($viewName),
                'loadChartUrl' => route(
                    'dms::package.module.chart.view.load',
                    [$packageName, $moduleName, $chart->getName(), $viewName]
                ),
            ]);
    }
}