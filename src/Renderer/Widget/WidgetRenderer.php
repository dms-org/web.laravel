<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Widget\IWidget;

/**
 * The widget renderer base class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class WidgetRenderer implements IWidgetRenderer
{
    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function render(IWidget $widget) : string
    {
        if (!$this->accepts($widget)) {
            throw InvalidArgumentException::format('Invalid widget supplied to %s',
                get_class($this) . '::' . __FUNCTION__);
        }

        return $this->renderWidget($widget);
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IWidget $widget
     *
     * @return string
     */
    abstract protected function renderWidget(IWidget $widget) : string;
}