<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Core\Widget\IWidget;

/**
 * The widget renderer base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class WidgetRenderer implements IWidgetRenderer
{
    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getLinks(IModule $module, IWidget $widget) : array
    {
        if (!$this->accepts($module, $widget)) {
            throw InvalidArgumentException::format(
                'Invalid widget supplied to %s',
                get_class($this) . '::' . __FUNCTION__
            );
        }

        return $this->getWidgetLinks($module, $widget);
    }

    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return array
     */
    abstract protected function getWidgetLinks(IModule $module, IWidget $widget) : array;

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function render(IModule $module, IWidget $widget) : string
    {
        if (!$this->accepts($module, $widget)) {
            throw InvalidArgumentException::format(
                'Invalid widget supplied to %s',
                get_class($this) . '::' . __FUNCTION__
            );
        }

        return $this->renderWidget($module, $widget);
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     */
    abstract protected function renderWidget(IModule $module, IWidget $widget) : string;
}