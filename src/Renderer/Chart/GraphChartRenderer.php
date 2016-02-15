<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\AreaChart;
use Dms\Core\Table\Chart\Structure\BarChart;
use Dms\Core\Table\Chart\Structure\GraphChart;
use Dms\Core\Table\Chart\Structure\LineChart;

/**
 * The chart renderer for graph charts (eg line, area, bar charts)
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GraphChartRenderer extends ChartRenderer
{
    /**
     * Returns whether this renderer can render the supplied chart.
     *
     * @param IChartDataTable $chartData
     *
     * @return bool
     */
    public function accepts(IChartDataTable $chartData) : bool
    {
        return $chartData->getStructure() instanceof GraphChart;
    }

    /**
     * @param IChartDataTable $chartData
     *
     * @return string
     */
    protected function renderChart(IChartDataTable $chartData) : string
    {
        /** @var GraphChart $chartStructure */
        $chartStructure = $chartData->getStructure();

        $yAxisKeys = [];
        $yAxisLabels = [];

        $yCounter = 1;

        foreach ($chartStructure->getVerticalAxis()->getComponents() as $component) {
            $yAxisKeys[] = 'y' . $yCounter++;
            $yAxisLabels[] = $component->getLabel();
        }

        $chartDataArray = $this->transformChartDataToIndexedArrays(
            $chartData,
            $chartStructure->getHorizontalAxis()->getName(),
            $chartStructure->getHorizontalAxis()->getComponent()->getName(),
            $chartStructure->getVerticalAxis()->getName()
        );

        return (string)view('dms::components.chart.graph-chart')
            ->with([
                'chartType'          => $this->getChartType($chartStructure),
                'data'               => $chartDataArray,
                'horizontalAxisKey'  => 'x',
                'verticalAxisKeys'   => $yAxisKeys,
                'verticalAxisLabels' => $yAxisLabels,
            ]);
    }

    private function transformChartDataToIndexedArrays(IChartDataTable $data, $xAxisName, $xComponentName, $yAxisName)
    {
        $results = [];

        foreach ($data->getRows() as $row) {
            $resultRow = [];

            $resultRow['x'] = $row[$xAxisName][$xComponentName];

            $yCounter = 1;

            foreach ($row[$yAxisName] as $yComponentValue) {
                $resultRow['y' . $yCounter++] = $yComponentValue;
            }

            $results[] = $resultRow;
        }

        return $results;
    }

    private function getChartType(GraphChart $chartStructure)
    {
        switch (true) {
            case $chartStructure instanceof LineChart:
                return 'line';
            case $chartStructure instanceof AreaChart:
                return 'area';
            case $chartStructure instanceof BarChart:
                return 'bar';

            default:
                throw InvalidArgumentException::format('Unknown chart type %s', get_class($chartStructure));
        }
    }
}