<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Core\Widget\IWidget;

/**
 * The widget renderer collection.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class WidgetRendererCollection
{
    /**
     * @var IWidgetRenderer[]
     */
    protected $widgetRenderers;

    /**
     * WidgetRendererCollection constructor.
     *
     * @param IWidgetRenderer[] $widgetRenderers
     */
    public function __construct(array $widgetRenderers)
    {
        InvalidArgumentException::verifyAllInstanceOf(
            __METHOD__, 'widgetRenderers', $widgetRenderers, IWidgetRenderer::class
        );

        $this->widgetRenderers = $widgetRenderers;
    }

    /**
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return IWidgetRenderer
     * @throws UnrenderableWidgetException
     */
    public function findRendererFor(IModule $module, IWidget $widget) : IWidgetRenderer
    {
        foreach ($this->widgetRenderers as $renderer) {
            if ($renderer->accepts($module, $widget)) {
                return $renderer;
            }
        }

        throw UnrenderableWidgetException::format(
            'Could not render widget \'%s\' of type %s: no matching renderer could be found',
            $widget->getName(), get_class($widget)
        );
    }
}