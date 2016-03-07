<?php

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Web\Laravel\Document\PublicFileModule;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Table\TableRenderer;
use Dms\Web\Laravel\Renderer\Widget\WidgetRendererCollection;

/**
 * The file tree module renderer.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class FileTreeModuleRenderer extends ModuleRenderer
{
    /**
     * @var TableRenderer
     */
    protected $tableRenderer;

    /**
     * ReadModuleRenderer constructor.
     *
     * @param TableRenderer            $tableRenderer
     * @param WidgetRendererCollection $widgetRenderers
     */
    public function __construct(TableRenderer $tableRenderer, WidgetRendererCollection $widgetRenderers)
    {
        parent::__construct($widgetRenderers);
        $this->tableRenderer = $tableRenderer;
    }

    /**
     * Returns whether this renderer can render the supplied module.
     *
     * @param ModuleContext $moduleContext
     *
     * @return bool
     */
    public function accepts(ModuleContext $moduleContext) : bool
    {
        return $moduleContext->getModule() instanceof PublicFileModule;
    }

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param ModuleContext $moduleContext
     *
     * @return string
     */
    protected function renderDashboard(ModuleContext $moduleContext) : string
    {
        /** @var PublicFileModule $module */
        $module        = $moduleContext->getModule();
        $rootDirectory = $module->getRootDirectory();
        $directoryTree = $module->getDirectoryTree();
        $summaryTable  = $module->getSummaryTable();

        return view('dms::package.module.dashboard.file-tree')
            ->with([
                'moduleContext' => $moduleContext,
                'directoryTree' => $directoryTree,
                'tableRenderer' => $this->tableRenderer,
                'summaryTable'  => $summaryTable,
                'rootDirectory' => $rootDirectory,
            ])
            ->render();
    }
}