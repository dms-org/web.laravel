<?php

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Form\IField;

/**
 * The field renderer interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFieldRenderer
{
    /**
     * Gets the expected class of the field type for the field.
     *
     * @return string
     */
    public function getFieldTypeClass();

    /**
     * Returns whether this renderer can render the supplied field.
     *
     * @param IField $field
     *
     * @return bool
     */
    public function accepts(IField $field);

    /**
     * Renders the supplied field as a html string.
     *
     * @param IField $field
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(IField $field);

    /**
     * Sets the parent field renderer.
     *
     * @param FieldRendererCollection $fieldRenderer
     *
     * @return void
     */
    public function setRendererCollection(FieldRendererCollection $fieldRenderer);
}