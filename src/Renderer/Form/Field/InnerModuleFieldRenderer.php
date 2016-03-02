<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerCrudModuleType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Action\ActionService;
use Dms\Web\Laravel\Http\ModuleRequestRouter;
use Dms\Web\Laravel\Renderer\Form\IFieldRendererWithActions;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The inner-module field renderer
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class InnerModuleFieldRenderer extends BladeFieldRendererWithActions implements IFieldRendererWithActions
{
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
     * @param string     $actionName
     * @param array      $data
     *
     * @return Response
     */
    protected function handleFieldAction(IField $field, IFieldType $fieldType, string $actionName, array $data) : Response
    {
        /** @var InnerCrudModuleType $fieldType */
        /** @var ICrudModule $module */
        $module = $fieldType->getModule();

        /** @var ModuleRequestRouter $moduleRequestRouter */
        $moduleRequestRouter = app(ModuleRequestRouter::class);

        /** @var Request $request */
        $request = Request::create($actionName, $data['_method'] ?? request()->method(), $data);

        return $moduleRequestRouter->dispatch($module, $request);
    }
}