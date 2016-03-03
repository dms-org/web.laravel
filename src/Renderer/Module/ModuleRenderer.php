<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Web\Laravel\Http\ModuleContext;
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
     * @param ModuleContext $moduleContext
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function render(ModuleContext $moduleContext) : string
    {
        if (!$this->accepts($moduleContext)) {
            throw InvalidArgumentException::format(
                'Invalid module \'%s\' supplied to %s',
                $module->getName(), get_class($this) . '::' . __FUNCTION__
            );
        }

        return $this->renderDashboard($moduleContext);
    }

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param ModuleContext $moduleContext
     *
     * @return string
     */
    abstract protected function renderDashboard(ModuleContext $moduleContext) : string;
}