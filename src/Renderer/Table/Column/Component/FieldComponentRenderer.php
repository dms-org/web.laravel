<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Table\Column\Component;

use Dms\Core\Model\Criteria\Condition\ConditionOperator;
use Dms\Core\Table\IColumnComponent;
use Dms\Web\Laravel\Renderer\Form\IFieldRenderer;
use Dms\Web\Laravel\Renderer\Table\IColumnComponentRenderer;

/**
 * The field component renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FieldComponentRenderer implements IColumnComponentRenderer
{
    /**
     * @var IFieldRenderer
     */
    protected $fieldRenderer;

    /**
     * FieldComponentRenderer constructor.
     *
     * @param IFieldRenderer $fieldRenderer
     */
    public function __construct(IFieldRenderer $fieldRenderer)
    {
        $this->fieldRenderer = $fieldRenderer;
    }

    /**
     * @param IColumnComponent $component
     *
     * @return bool
     */
    public function accepts(IColumnComponent $component) : bool
    {
        return $this->fieldRenderer->accepts($component->getType()->getOperator(ConditionOperator::EQUALS)->getField());
    }

    /**
     * Renders the supplied column component value as a html string.
     *
     * @param IColumnComponent $component
     * @param mixed            $value
     *
     * @return string
     */
    public function render(IColumnComponent $component, $value) : string
    {
        $field = $component->getType()->getOperator(ConditionOperator::EQUALS)->getField();

        return $this->fieldRenderer->renderValue(
            $field, $field->unprocess($value)
        );
    }
}