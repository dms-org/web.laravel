<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection;

/**
 * The module dashboard renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class ModuleRenderer implements IModuleRenderer
{
    /**
     * @var WidgetRendererCollection
     */
    protected $widgetRenderers;

    /**
     * ModuleRenderer constructor.
     *
     * @param WidgetRendererCollection $widgetRenderers
     */
    public function __construct(WidgetRendererCollection $widgetRenderers)
    {
        $this->widgetRenderers = $widgetRenderers;
    }

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param IModule $module
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function render(IModule $module)
    {
        if (!$this->accepts($module)) {
            throw InvalidArgumentException::format(
                'Invalid module \'%s\' supplied to %s',
                $module->getName(), get_class($this) . '::' . __FUNCTION__
            );
        }

        return $this->renderDashboard($module);
    }

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param IModule $module
     *
     * @return string
     */
    abstract protected function renderDashboard(IModule $module);
}