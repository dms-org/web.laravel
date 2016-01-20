<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Module\IModule;

/**
 * The default module renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DefaultModuleRenderer extends ModuleRenderer
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
        return true;
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
        $authorizedWidgets = [];

        foreach ($module->getWidgets() as $widget) {
            if ($widget->isAuthorized()) {
                $authorizedWidgets[] = $widget;
            }
        }

        return (string)view('dms::package.module.dashboard.default')
            ->with([
                'widgets'         => $authorizedWidgets,
                'widgetRenderers' => $this->widgetRenderers,
            ]);
    }
}