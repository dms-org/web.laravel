<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Illuminate\Http\Response;

/**
 * The field renderer with actions interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldRendererWithActions
{
    /**
     * @param string $actionName
     * @param array  $data
     *
     * @return Response
     */
    public function handleAction(string $actionName, array $data) : Response;
}