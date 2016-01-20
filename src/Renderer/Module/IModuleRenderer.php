<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;

/**
 * The module dashboard renderer interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IModuleRenderer
{
    /**
     * Returns whether this renderer can render the supplied module.
     *
     * @param IModule $module
     *
     * @return bool
     */
    public function accepts(IModule $module);

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param IModule $module
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(IModule $module);
}