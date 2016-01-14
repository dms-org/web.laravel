<?php

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Exception\InvalidArgumentException;
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
     * @param IWidget $widget
     *
     * @return bool
     */
    public function accepts(IWidget $widget);

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(IWidget $widget);
}