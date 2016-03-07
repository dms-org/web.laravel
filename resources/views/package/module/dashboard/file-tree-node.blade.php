<?php /** @var \Dms\Web\Laravel\Http\ModuleContext $moduleContext */ ?>
<?php /** @var \Dms\Web\Laravel\Document\PublicFileModule $module */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\TableRenderer $tableRenderer */ ?>
<?php /** @var \Dms\Core\Common\Crud\Table\ISummaryTable $summaryTable */ ?>
<?php /** @var string $rootDirectory */ ?>
<?php /** @var \Dms\Web\Laravel\Document\DirectoryTree $directoryTree */ ?>

<ul class="list-group dms-object-list">
    @foreach($directoryTree->subDirectories as $subDirectory)
        <li class="list-group-item dms-folder-item dms-folder-closed" data-folder-path="{{ substr($subDirectory->directory->getFullPath(), strlen($rootDirectory)) }}">
            <i class="fa fa-folder"></i>
            <i class="fa fa-folder-open"></i>
            {{ $subDirectory->getName() }}

            @include('dms::package.module.dashboard.file-tree-node', ['directoryTree' => $subDirectory])
        </li>
    @endforeach

    @foreach ($directoryTree->files as $file)
        <?php $fileId = $module->getDataSource()->getObjectId($file) ?>
        <li class="list-group-item dms-file-item" data-id="{{ $fileId }}">
            <img src="{{ asset('vendor/dms/img/file/icon/' . strtolower($file->getExtension()) . '.png') }}"/>
            {{ $file->getClientFileNameWithFallback() }}

            <span class="dms-file-action-buttons pull-right">

                <span class="dms-run-action-form inline"
                      data-action="{{ $moduleContext->getUrl('action.run', ['download', 'object' => $fileId]) }}"
                      data-method="post">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-xs btn-success">
                        <i class="fa fa-download"></i>
                    </button>
                </span>
                <a href="{{ $moduleContext->getUrl('action.show', ['details', $fileId]) }}" title="View Details"
                   class="btn btn-xs btn-info">
                    <i class="fa fa-bars"></i>
                </a>
                <a href="{{ $moduleContext->getUrl('action.form', ['edit', $fileId]) }}" title="Edit"
                   class="btn btn-xs btn-primary">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
                <span class="dms-run-action-form inline"
                     data-action="{{ $moduleContext->getUrl('action.run', ['remove', 'object' => $fileId]) }}"
                     data-after-run-remove-closest="tr"
                     data-method="post">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-xs btn-danger">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </span>
            </span>
        </li>
    @endforeach


    @if ($directoryTree->subDirectories->count() + $directoryTree->files->count() === 0)
        <li class="list-group-item">
            <div class="help-block">This folder is empty</div>
        </li>
    @endif
</ul>