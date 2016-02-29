<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\Geo\Form\LatLngType;
use Dms\Common\Structure\Geo\Form\StreetAddressType;
use Dms\Common\Structure\Geo\Form\StreetAddressWithLatLngType;
use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;

/**
 * The address field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AddressFieldRenderer extends BladeFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [LatLngType::class, StreetAddressType::class, StreetAddressWithLatLngType::class];
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
        return $this->renderView(
            $field,
            'dms::components.field.map.input',
            [

            ],
            [
                'inputMode' => $this->getInputMode($fieldType),
            ]
        );
    }

    protected function getInputMode(IFieldType $fieldType) : string
    {
        if ($fieldType instanceof LatLngType) {
            return 'lat-lng';
        }

        if ($fieldType instanceof StreetAddressType) {
            return 'address';
        }

        if ($fieldType instanceof StreetAddressWithLatLngType) {
            return 'address-with-lat-lng';
        }

        throw InvalidArgumentException::format('Unknown address field type: %s', get_class($fieldType));
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
        $address = null;
        $latLng  = null;

        if ($fieldType instanceof StreetAddressType) {
            $address = $value;
        }

        if ($fieldType instanceof LatLngType) {
            $latLng = $value;
        }

        if ($fieldType instanceof StreetAddressWithLatLngType) {
            $address = $value ? $value['address'] : null;
            $latLng  = $value ? $value['coordinates'] : null;
        }

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.map.value',
            [
                'address' => $address,
                'latLng'  => $latLng,
            ]
        );
    }
}