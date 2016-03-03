<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Form\IField;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The field renderer with actions interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldRendererWithActions
{
    /**
     * @param FormRenderingContext $renderingContext
     * @param IField               $field
     * @param Request              $request
     * @param string               $actionName
     * @param array                $data
     *
     * @return Response
     */
    public function handleAction(FormRenderingContext $renderingContext, IField $field, Request $request, string $actionName = null, array $data);
}