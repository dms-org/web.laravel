<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Module\IModule;
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

    public function accepts(IModule $module, IWidget $widget) : bool
    {
        return $widget instanceof ChartWidget;
    }

    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return array
     */
    protected function getWidgetLinks(IModule $module, IWidget $widget) : array
    {
        /** @var ChartWidget $widget */
        $chartDisplay = $widget->getChartDisplay();

        $links = [];

        foreach ($chartDisplay->getViews() as $chartView) {
            $viewParams = [$module->getPackageName(), $module->getName(), $chartDisplay->getName(), $chartView->getName()];

            $links[route('dms::package.module.chart.view.show', $viewParams)] = $chartView->getLabel();
        }

        return $links;
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IModule $module, IWidget $widget) : string
    {
        /** @var ChartWidget $widget */
        $chartData = $widget->loadData();

        return view('dms::components.widget.chart')
            ->with([
                'chartContent' => $this->chartRenderers->findRendererFor($chartData)->render($chartData),
            ])
            ->render();
    }
}