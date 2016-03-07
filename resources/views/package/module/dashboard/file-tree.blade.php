<?php /** @var \Dms\Web\Laravel\Http\ModuleContext $moduleContext */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\TableRenderer $tableRenderer */ ?>
<?php /** @var \Dms\Core\Common\Crud\Table\ISummaryTable $summaryTable */ ?>
<?php /** @var string $rootDirectory */ ?>
<?php /** @var \Dms\Web\Laravel\Document\DirectoryTree $directoryTree */ ?>
<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-default dms-file-tree"
             data-move-directory-url="{{ $moduleContext->getUrl('action.run', ['move-folder', '__object__']) }}"
             data-reload-file-tree-url="{{ $moduleContext->getUrl('dashboard') }}"
        >
            <div class="panel-body dms-upload-form">
                {!! app(\Dms\Web\Laravel\Renderer\Form\ActionFormRenderer::class)->renderActionForm($moduleContext, $moduleContext->getModule()->getAction('upload-files')) !!}
            </div>

            <div class="panel-body">
                <div class="dms-quick-filter-form">
                    <div class="input-group pull-right">
                        <span class="input-group-addon">Filter</span>

                        <div class="form-group">
                            <input name="filter" class="form-control" type="text" placeholder="Filter by name..."/>
                        </div>

                        <span class="input-group-btn">
                            <button class="btn btn-info" type="button"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="dms-file-tree-data-container">
                <div class="dms-file-tree-data">
                    @include('dms::package.module.dashboard.file-tree-node', [
                        'moduleContext' => $moduleContext,
                        'module'        => $moduleContext->getModule(),
                        'directoryTree' => $directoryTree,
                    ])
                </div>

                @include('dms::partials.spinner')
            </div>
        </div>
    </div>
</div>
<!-- /.col -->
</div>