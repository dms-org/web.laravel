<?php

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\PieChart;

/**
 * The chart renderer for pie charts
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PieChartRenderer extends ChartRenderer
{
    /**
     * Returns whether this renderer can render the supplied chart.
     *
     * @param IChartDataTable $chartData
     *
     * @return bool
     */
    public function accepts(IChartDataTable $chartData)
    {
        return $chartData->getStructure() instanceof PieChart;
    }

    /**
     * @param IChartDataTable $chartData
     *
     * @return string
     */
    protected function renderChart(IChartDataTable $chartData)
    {
        /** @var PieChart $chartStructure */
        $chartStructure = $chartData->getStructure();

        $chartDataArray = $this->transformChartDataToIndexedArrays(
            $chartData,
            $chartStructure->getTypeAxis()->getName(),
            $chartStructure->getTypeAxis()->getComponent()->getName(),
            $chartStructure->getValueAxis()->getName(),
            $chartStructure->getValueAxis()->getComponent()->getName()
        );

        return (string)view('dms::components.chart.pie-chart')
            ->with([
                'data' => $chartDataArray,
            ]);
    }

    private function transformChartDataToIndexedArrays(
        IChartDataTable $data,
        $labelAxisName,
        $labelComponentName,
        $valueAxisName,
        $valueComponentName
    ) {
        $results = [];

        foreach ($data->getRows() as $row) {
            $results[] = [
                'label' => $row[$labelAxisName][$labelComponentName],
                'value' => $row[$valueAxisName][$valueComponentName],
            ];
        }

        return $results;
    }
}