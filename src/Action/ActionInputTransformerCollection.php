<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IParameterizedAction;

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
     * @param IParameterizedAction $action
     * @param array                $input
     *
     * @return array
     */
    public function transform(IParameterizedAction $action, array $input) : array
    {
        foreach ($this->transformers as $transformer) {
            $input = $transformer->transform($action, $input);
        }

        return $input;
    }
}