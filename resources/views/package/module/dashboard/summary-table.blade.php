<?php /** @var \Dms\Web\Laravel\Renderer\Table\TableRenderer $tableRenderer */ ?>
<?php /** @var \Dms\Core\Module\IModule $module */ ?>
<?php /** @var \Dms\Core\Common\Crud\Table\ISummaryTable $summaryTable */ ?>
<?php /** @var \Dms\Core\Module\ITableView[] $summaryTableViews */ ?>
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom dms-table-tabs">
            <ul class="nav nav-tabs">
                @foreach($summaryTableViews as $view)
                    <li class="{{ $activeViewName === $view->getName() ? 'active' : '' }}">
                        <a class="dms-table-tab-show-button" href="#summary-table-tab-{{ $view->getName() }}" data-toggle="tab">{{ $view->getLabel() }}</a>
                    </li>
                @endforeach
                @if($createActionName ?? false)
                    <li class="pull-right">
                        <a class="btn btn-success" href="{{ route('dms::package.module.action.form', [$packageName, $moduleName, $createActionName]) }}">
                            Add <i class="fa fa-plus-circle"></i>
                        </a>
                    </li>
                @endif
            </ul>
            <div class="tab-content">
                @foreach($summaryTableViews as $view)
                    <div class="tab-pane {{ $view->isDefault() ? 'active' : '' }}" id="summary-table-tab-{{ $view->getName() }}">
                        {!! $tableRenderer->renderTableControl($module, $summaryTable, $view->getName()) !!}
                    </div>
                    <!-- /.tab-pane -->
                @endforeach
            </div>
            <!-- /.tab-content -->
        </div>
        <!-- nav-tabs-custom -->
    </div>
    <!-- /.col -->
</div>