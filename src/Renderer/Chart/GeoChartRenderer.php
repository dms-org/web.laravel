<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Common\Structure\Geo\Chart\GeoChart;
use Dms\Core\Table\Chart\IChartDataTable;

/**
 * The chart renderer for geo charts
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GeoChartRenderer extends PieChartRenderer
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
        return $chartData->getStructure() instanceof GeoChart;
    }

    /**
     * @param IChartDataTable $chartData
     *
     * @return string
     */
    protected function renderChart(IChartDataTable $chartData) : string
    {
        /** @var GeoChart $chartStructure */
        $chartStructure = $chartData->getStructure();

        $chartDataArray = $this->transformChartDataToIndexedArrays(
            $chartData,
            $chartStructure->getTypeAxis()->getName(),
            $chartStructure->getTypeAxis()->getComponent()->getName(),
            $chartStructure->getValueAxis()->getName(),
            $chartStructure->getValueAxis()->getComponent()->getName()
        );

        return view('dms::components.chart.geo-chart')
            ->with([
                'valueLabel' => $chartStructure->getValueAxis()->getComponent()->getLabel(),
                'data'       => $chartDataArray,
            ])
            ->render();
    }
}