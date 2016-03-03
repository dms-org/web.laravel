<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package\Module;

use Dms\Core\ICms;
use Dms\Core\Package\IPackage;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Renderer\Module\ModuleRendererCollection;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Request;

/**
 * The module controller.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ModuleController extends DmsController
{
    /**
     * @var IPackage
     */
    protected $package;

    /**
     * @var IPackage
     */
    protected $module;

    /**
     * @var ModuleRendererCollection
     */
    protected $moduleRenderers;

    /**
     * ModuleController constructor.
     *
     * @param ICms                     $cms
     * @param ModuleRendererCollection $moduleRenderers
     */
    public function __construct(ICms $cms, ModuleRendererCollection $moduleRenderers)
    {
        parent::__construct($cms);
        $this->moduleRenderers = $moduleRenderers;
    }

    /**
     * @param ModuleContext $moduleContext
     *
     * @return mixed
     */
    public function showDashboard(ModuleContext $moduleContext)
    {
        $module = $moduleContext->getModule();

        return view('dms::package.module.dashboard')
            ->with([
                'assetGroups'     => ['tables', 'charts'],
                'pageTitle'       => implode(' :: ', $moduleContext->getTitles()),
                'breadcrumbs'     => array_slice($moduleContext->getBreadcrumbs(), 0, -1, true),
                'finalBreadcrumb' => StringHumanizer::title($moduleContext->getModule()->getName()),
                'moduleContent'   => $this->moduleRenderers->findRendererFor($moduleContext)->render($moduleContext),
            ]);
    }
}