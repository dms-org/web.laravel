<?php

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\PieChart;
use Dms\Core\Widget\ChartWidget;

/**
 * The widget renderer for pie charts
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PieChartWidgetRenderer extends ChartWidgetRenderer
{
    /**
     * @param ChartWidget $widget
     *
     * @return bool
     */
    protected function acceptsChartWidget(ChartWidget $widget)
    {
        return $widget->getChartDataSource()->getStructure() instanceof PieChart;
    }

    /**
     * @param ChartWidget $widget
     *
     * @return string
     */
    protected function renderWidgetChart(ChartWidget $widget)
    {
        /** @var PieChart $chartStructure */
        $chartStructure = $widget->getChartDataSource()->getStructure();

        $chartData = $this->transformChartDataToIndexedArrays(
                $widget->loadData(),
                $chartStructure->getTypeAxis()->getName(),
                $chartStructure->getTypeAxis()->getComponent()->getName(),
                $chartStructure->getValueAxis()->getName(),
                $chartStructure->getValueAxis()->getComponent()->getName()
        );

        return (string)view('dms::components.chart.pie-chart')
                ->with([
                        'data' => $chartData,
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