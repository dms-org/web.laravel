<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form\Field;

use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\Field\Type\FieldType;
use Dms\Core\Form\Field\Type\InnerCrudModuleType;
use Dms\Core\Form\IField;
use Dms\Core\Form\IFieldType;
use Dms\Web\Laravel\Action\ActionService;
use Dms\Web\Laravel\Http\ModuleRequestRouter;
use Dms\Web\Laravel\Renderer\Form\FormRenderingContext;
use Dms\Web\Laravel\Renderer\Form\IFieldRendererWithActions;
use Dms\Web\Laravel\Renderer\Module\ReadModuleRenderer;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Request;

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

    protected function canRender(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : bool
    {
        return !$fieldType->has(FieldType::ATTR_OPTIONS);
    }

    protected function renderField(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType) : string
    {
        $innerModuleContext = $this->loadInnerModuleContext($field, $renderingContext);

        /** @var ReadModuleRenderer $renderer */
        $renderer = app(ReadModuleRenderer::class);
        return $this->renderView(
            $field,
            'dms::components.field.inner-module.input',
            [],
            [
                'tableContent' => $renderer->render($innerModuleContext),
            ]
        );
    }

    protected function renderFieldValue(FormRenderingContext $renderingContext, IField $field, $value, IFieldType $fieldType) : string
    {
        /** @var InnerCrudModuleType $fieldType */
        /** @var ICrudModule $innerModule */
        $innerModule        = $fieldType->getModule();
        $innerModuleContext = $this->loadInnerModuleContext($field, $renderingContext);

        /** @var TableRenderer $tableRenderer */
        $tableRenderer = app(TableRenderer::class);

        return $this->renderView(
            $field,
            'dms::components.field.inner-module.input',
            [],
            [
                'tableContent' => $tableRenderer->renderTableData($innerModuleContext, $innerModule->getSummaryTable(), $innerModule->getSummaryTable()->getDataSource()->load()),
            ]
        );
    }

    protected function handleFieldAction(FormRenderingContext $renderingContext, IField $field, IFieldType $fieldType, Request $request, string $actionName = null, array $data)
    {
        $moduleContext = $this->loadInnerModuleContext($field, $renderingContext);

        /** @var ModuleRequestRouter $moduleRequestRouter */
        $moduleRequestRouter = app(ModuleRequestRouter::class);

        /** @var Request $request */
        $request = Request::create('/' . $actionName, request()->method(), $data);

        return $moduleRequestRouter->dispatch($moduleContext, $request);
    }

    protected function loadInnerModuleContext(IField $field, FormRenderingContext $renderingContext)
    {
        /** @var InnerCrudModuleType $fieldType */
        $fieldType = $field->getType();

        /** @var ICrudModule $module */
        $innerModule = $fieldType->getModule();

        $moduleContext = $renderingContext->getModuleContext();

        if ($renderingContext->getObjectId() !== null) {
            $subModulePath = $moduleContext->getUrl('action.form.object.stage.field.action', [
                $renderingContext->getAction()->getName(),
                $renderingContext->getObjectId(),
                $renderingContext->getCurrentStageNumber(),
                $field->getName(),
            ]);

            /** @var IReadModule $currentModule */
            $currentModule = $moduleContext->getModule();
            $moduleContext = $moduleContext->withBreadcrumb(
                $currentModule->getLabelFor($renderingContext->getObject()),
                $moduleContext->getUrl('action.form', [
                    $renderingContext->getAction()->getName(),
                    $renderingContext->getObjectId(),
                ])
            );
        } else {
            $subModulePath = $moduleContext->getUrl('action.form.stage.field.action', [
                $renderingContext->getAction()->getName(),
                $renderingContext->getCurrentStageNumber(),
                $field->getName(),
            ]);
        }

        return $moduleContext
            ->inSubModuleContext($innerModule, $subModulePath)
            ->withBreadcrumb(StringHumanizer::title($innerModule->getName()), $subModulePath);
    }
}