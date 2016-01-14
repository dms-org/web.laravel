<?php

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FieldRendererCollection;
use Dms\Web\Laravel\Renderer\Form\IFieldRenderer;

/**
 * The base field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
abstract class FieldRenderer implements IFieldRenderer
{
    /**
     * @var FieldRendererCollection
     */
    protected $fieldRendererCollection;

    /**
     * @var string
     */
    protected $fieldTypeClass;

    /**
     * FieldRenderer constructor.
     */
    public function __construct()
    {
        $this->fieldTypeClass = $this->getFieldTypeClass();
    }

    /**
     * @param FieldRendererCollection $fieldRenderer
     *
     * @return void
     */
    public function setRendererCollection(FieldRendererCollection $fieldRenderer)
    {
        $this->fieldRendererCollection = $fieldRenderer;
    }

    /**
     * Returns whether this renderer can render the supplied field.
     *
     * @param IField $field
     *
     * @return bool
     */
    final public function accepts(IField $field)
    {
        $type = $field->getType();

        if (!($type instanceof $this->fieldTypeClass)) {
            return false;
        }

        return $this->canRender($field, $type);
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return bool
     */
    abstract protected function canRender(IField $field, IFieldType $fieldType);

    /**
     * Renders the supplied field input as a html string.
     *
     * @param IField $field
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function render(IField $field)
    {
        if (!$this->accepts($field)) {
            throw InvalidArgumentException::format(
                    'Field \'%s\' cannot be rendered in renderer of type %s',
                    $field->getName(), get_class($this)
            );
        }

        return $this->renderField($field, $field->getType());
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    abstract protected function renderField(IField $field, IFieldType $fieldType);

    /**
     * Renders the supplied field value as a html string.
     *
     * @param IField $field
     *
     * @return string
     * @throws InvalidArgumentException
     */
    final public function renderValue(IField $field)
    {
        if (!$this->accepts($field)) {
            throw InvalidArgumentException::format(
                    'Field \'%s\' cannot be rendered in renderer of type %s',
                    $field->getName(), get_class($this)
            );
        }

        return $this->renderField($field, $field->getType());
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    abstract protected function renderFieldValue(IField $field, IFieldType $fieldType);
}