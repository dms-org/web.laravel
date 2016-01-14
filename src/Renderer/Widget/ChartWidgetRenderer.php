<?php

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\GraphChart;
use Dms\Core\Widget\ChartWidget;
use Dms\Core\Widget\IWidget;

/**
 * The widget renderer for charts
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ChartWidgetRenderer extends WidgetRenderer
{
    public function accepts(IWidget $widget)
    {
        if (!($widget instanceof ChartWidget)) {
            return false;
        }

        return $this->acceptsChartWidget($widget);
    }

    /**
     * @param ChartWidget $widget
     *
     * @return bool
     */
    abstract protected function acceptsChartWidget(ChartWidget $widget);

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IWidget $widget)
    {
        /** @var ChartWidget $widget */
        return (string)view('dms::components.widget.chart')
                ->with([
                    'chartContent' => $this->renderWidgetChart($widget)
                ]);
    }

    /**
     * @param ChartWidget $widget
     *
     * @return string
     */
    abstract protected function renderWidgetChart(ChartWidget $widget);
}