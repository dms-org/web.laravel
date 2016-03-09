<?php /** @var \Dms\Web\Laravel\Http\ModuleContext $moduleContext */ ?>
<?php /** @var \Dms\Web\Laravel\Document\PublicFileModule $module */ ?>
<?php /** @var string $rootDirectory */ ?>
<?php /** @var \Dms\Web\Laravel\Document\DirectoryTree $directoryTree */ ?>

<ul class="list-group dms-object-list">
    @foreach($directoryTree->subDirectories as $subDirectory)
        <li class="list-group-item dms-folder-item dms-folder-closed"
            data-folder-path="{{ substr($subDirectory->directory->getFullPath(), strlen($rootDirectory)) }}">
            <i class="fa fa-folder"></i>
            <i class="fa fa-folder-open"></i>
            {{ $subDirectory->getName() }}

            @include('dms::package.module.dashboard.file-tree-node', ['directoryTree' => $subDirectory])
        </li>
    @endforeach

    <li class="list-group-item dms-file-list clearfix">
        <div class="row">
            @foreach ($directoryTree->files as $file)
                <?php $fileId = $dataSource->getObjectId($file) ?>
                <?php $isImage = @(bool)getimagesize($file->getFullPath()) ?>

                <div class="col-sm-4 dms-file-item{{ $isImage ? ' dms-image-item' : '' }}"
                     data-id="{{ $fileId }}"
                     @if ($isPublic)
                     data-public-url="{{ asset(config('dms.storage.public-files.url') . '/' . substr($file->getFullPath(), strlen($rootDirectory))) }}"
                    @endif
                >
                    <div class="panel panel-default">
                        <div class="panel-heading" style="text-overflow: ellipsis; white-space: nowrap;overflow: hidden">
                            <img src="{{ asset('vendor/dms/img/file/icon/' . strtolower($file->getExtension()) . '.png') }}"/>

                            {{ $file->getClientFileNameWithFallback() }}
                        </div>

                        <div class="panel-body">
                            @if ($isPublic && $isImage)
                                <div class="dms-image-preview">
                                    <img src="{{ asset(config('dms.storage.public-files.url') . '/' . substr($file->getFullPath(), strlen($rootDirectory))) }}"/>
                                </div>
                            @else
                                <p><strong>Size: </strong> {{ \Dms\Web\Laravel\Util\FileSizeFormatter::formatBytes($file->getSize()) }}</p>
                                <p>
                                    <strong>Created At: </strong>
                                    {{ Dms\Common\Structure\DateTime\DateTime::fromString('@' . $file->getInfo()->getCTime())->format(\Dms\Common\Structure\DateTime\DateTime::DISPLAY_FORMAT) }}
                                </p>
                                <p>
                                    <strong>Modified At: </strong>
                                    {{ Dms\Common\Structure\DateTime\DateTime::fromString('@' . $file->getInfo()->getMTime())->format(\Dms\Common\Structure\DateTime\DateTime::DISPLAY_FORMAT) }}
                                </p>
                            @endif
                        </div>

                        <div class="panel-footer dms-file-action-buttons ">
                            @if($isTrash ?? false)
                                <span class="dms-run-action-form inline"
                                      data-action="{{ $moduleContext->getUrl('action.run', ['restore-file', 'file' => $fileId]) }}"
                                      data-after-run-remove-closest="li"
                                      data-after-run-refresh="1"
                                      data-method="post">
                                {!! csrf_field() !!}
                                    <button type="submit" class="btn btn-xs btn-success">
                                        <i class="fa fa-life-ring"></i>
                                    </button>
                            </span>
                            @else
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
                                      data-after-run-remove-closest="li"
                                      data-method="post">
                                {!! csrf_field() !!}
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </li>


    @if ($directoryTree->subDirectories->count() + $directoryTree->files->count() === 0)
        <li class="list-group-item">
            <div class="help-block">This folder is empty</div>
        </li>
    @endif
</ul>