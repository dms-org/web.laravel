<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\DateTime\Form\DateOrTimeRangeType;
use Dms\Common\Structure\DateTime\Form\DateRangeType;
use Dms\Common\Structure\DateTime\Form\TimeRangeType;
use Dms\Common\Structure\DateTime\Form\TimezonedDateTimeRangeType;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Type\DateTimeTypeBase;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The date/time range field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DateOrTimeRangeFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass() : string
    {
        return DateOrTimeRangeType::class;
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    protected function canRender(IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderField(IField $field, IFieldType $fieldType) : string
    {
        $mode = $this->getMode($fieldType);

        return $this->renderView(
            $field,
            'dms::components.field.date-or-time.range.input',
            [
                DateTimeTypeBase::ATTR_FORMAT => 'format',
                DateTimeTypeBase::ATTR_MIN    => 'min',
                DateTimeTypeBase::ATTR_MAX    => 'max',
                // TODO: less_than and greater_than
            ],
            [
                'mode' => $mode,
            ]
        );
    }

    /**
     * @param IField     $field
     * @param mixed      $value
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, $value, IFieldType $fieldType) : string
    {
        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.date-or-time.range.value',
            [
                'format' => $fieldType->get(DateTimeTypeBase::ATTR_FORMAT),
            ]
        );
    }

    private function getMode(IFieldType $fieldType) : string
    {
        if ($fieldType instanceof DateRangeType) {
            return 'date';
        }

        if ($fieldType instanceof TimeRangeType) {
            return 'time';
        }

        if ($fieldType instanceof DateOrTimeRangeType) {
            return 'date-time';
        }

        if ($fieldType instanceof TimezonedDateTimeRangeType) {
            return 'timezoned-date-time';
        }

        throw InvalidArgumentException::format('Unknown date range field type: %s', get_class($fieldType));
    }
}