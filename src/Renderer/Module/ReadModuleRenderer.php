<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Module\IModule;
use Dms\Core\Module\ITableView;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
use Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection;

/**
 * The read module renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ReadModuleRenderer extends ModuleRenderer
{
    /**
     * @var TableRenderer
     */
    protected $tableRenderer;

    /**
     * ReadModuleRenderer constructor.
     *
     * @param TableRenderer            $tableRenderer
     * @param WidgetRendererCollection $widgetRenderers
     */
    public function __construct(TableRenderer $tableRenderer, WidgetRendererCollection $widgetRenderers)
    {
        parent::__construct($widgetRenderers);
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * Returns whether this renderer can render the supplied module.
     *
     * @param IModule $module
     *
     * @return bool
     */
    public function accepts(IModule $module) : bool
    {
        return $module instanceof IReadModule;
    }

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param IModule $module
     *
     * @return string
     */
    protected function renderDashboard(IModule $module) : string
    {
        /** @var IReadModule $module */
        $summaryTable = $module->getSummaryTable();

        /** @var ITableView[] $views */
        $views = $summaryTable->getViews() ?: [$summaryTable->getDefaultView()];

        $createActionName = null;
        if ($module instanceof ICrudModule) {
            /** @var ICrudModule $module */
            if ($module->allowsCreate()) {
                $createActionName = $module->getCreateAction()->getName();
            }
        }

        $activeViewName = session('initial-view-name') && $summaryTable->hasView(session('initial-view-name'))
            ? session('initial-view-name')
            : $summaryTable->getDefaultView()->getName();

        return view('dms::package.module.dashboard.summary-table')
            ->with([
                'packageName'       => $module->getPackageName(),
                'moduleName'        => $module->getName(),
                'tableRenderer'     => $this->tableRenderer,
                'module'            => $module,
                'summaryTable'      => $summaryTable,
                'summaryTableViews' => $views,
                'activeViewName'    => $activeViewName,
                'createActionName'  => $createActionName,
            ])
            ->render();
    }
}