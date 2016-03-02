<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Common\Structure\DateTime\Form\DateOrTimeRangeType;
use Dms\Common\Structure\FileSystem\Form\FileUploadType;
use Dms\Common\Structure\FileSystem\Form\ImageUploadType;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerCrudModuleType;
use Dms\Core\Form\Field\Type\InnerFormType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Renderer\Form\FormRenderer;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;

/**
 * The inner-module field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerModuleFieldRenderer extends BladeFieldRenderer
{
    /**
     * InnerModuleFieldRenderer constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Gets the expected class of the field type for the field.
     *
     * @return array
     */
    public function getFieldTypeClasses() : array
    {
        return [InnerCrudModuleType::class];
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
        /** @var InnerCrudModuleType $fieldType */
        /** @var ICrudModule $module */
        $module = $fieldType->getModule();

        /** @var TableRenderer $tableRenderer */
        $tableRenderer = app(TableRenderer::class);

        return $this->renderView(
            $field,
            'dms::components.field.inner-module.input',
            [],
            [
                'tableContent' => $tableRenderer->renderTableData($module, $module->getSummaryTable(), $module->getSummaryTable()->getDataSource()->load()),
            ]
        );
    }

    /**
     * @param IField     $field
     * @param IFieldType $fieldType
     *
     * @return string
     */
    protected function renderFieldValue(IField $field, $value, IFieldType $fieldType) : string
    {
        /** @var InnerFormType $fieldType */
        $formWithArrayFields = $fieldType->getInnerArrayForm($field->getName());
        $formRenderer = new FormRenderer($this->fieldRendererCollection);

        return $this->renderValueViewWithNullDefault(
            $field, $value,
            'dms::components.field.inner-module.value',
            [
                'formContent' => $formRenderer->renderFields($formWithArrayFields),
            ]
        );
    }
}