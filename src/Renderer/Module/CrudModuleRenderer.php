<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Module\IModule;

/**
 * The crud module renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class CrudModuleRenderer extends ModuleRenderer
{
    /**
     * Returns whether this renderer can render the supplied module.
     *
     * @param IModule $module
     *
     * @return bool
     */
    public function accepts(IModule $module)
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
    protected function renderDashboard(IModule $module)
    {
        /** @var IReadModule $module */
        $module->getSummaryTable()->loadView()

        return (string)view('dms::package.module.dashboard.default')
            ->with([
                'widgets'         => $authorizedWidgets,
                'widgetRenderers' => $this->widgetRenderers,
            ]);
    }
}