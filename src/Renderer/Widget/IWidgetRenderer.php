<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Core\Widget\IWidget;

/**
 * The widget renderer interface
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IWidgetRenderer
{
    /**
     * Returns whether this renderer can render the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return bool
     */
    public function accepts(IModule $module, IWidget $widget) : bool;

    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getLinks(IModule $module, IWidget $widget) : array;

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(IModule $module, IWidget $widget) : string;
}