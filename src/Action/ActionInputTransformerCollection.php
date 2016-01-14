<?php

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IAction;

/**
 * The input transformer collection.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionInputTransformerCollection implements IActionInputTransformer
{
    /**
     * @var IActionInputTransformer[]
     */
    protected $transformers;

    /**
     * InputTransformerCollection constructor.
     *
     * @param IActionInputTransformer[] $transformers
     */
    public function __construct(array $transformers)
    {
        InvalidArgumentException::verifyAllInstanceOf(__METHOD__, 'transformers', $transformers, IActionInputTransformer::class);
        $this->transformers = $transformers;
    }

    /**
     * Transforms for the supplied action.
     *
     * @param IAction $action
     * @param array   $input
     *
     * @return array
     */
    public function transform(IAction $action, array $input)
    {
        foreach ($this->transformers as $transformer) {
            $input = $transformer->transform($action, $input);
        }

        return $input;
    }
}