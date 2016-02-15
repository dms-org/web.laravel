<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Widget\ChartWidget;
use Dms\Core\Widget\IWidget;
use Dms\Web\Laravel\Renderer\Chart\ChartRendererCollection;

/**
 * The widget renderer for charts
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartWidgetRenderer extends WidgetRenderer
{
    /**
     * @var ChartRendererCollection
     */
    protected $chartRenderers;

    /**
     * ChartWidgetRenderer constructor.
     *
     * @param ChartRendererCollection $chartRenderers
     */
    public function __construct(ChartRendererCollection $chartRenderers)
    {
        $this->chartRenderers = $chartRenderers;
    }

    public function accepts(IWidget $widget) : bool
    {
        return $widget instanceof ChartWidget;
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IWidget $widget) : string
    {
        /** @var ChartWidget $widget */
        $chartData = $widget->loadData();

        return (string)view('dms::components.widget.chart')
            ->with([
                'chartContent' => $this->chartRenderers->findRendererFor($chartData)->render($chartData),
            ]);
    }
}