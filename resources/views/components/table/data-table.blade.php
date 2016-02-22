<?php /** @var \Dms\Core\Table\IColumn[] $columns */ ?>
<?php /** @var \Dms\Core\Table\ITableSection[] $sections */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\IColumnRenderer[] $columnRenderers */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\RowAction\RowActionButton[] $rowActionButtons */ ?>
<table class="table dms-table">
    <thead>
    <tr>
        @foreach ($columns as $column)
            <th data-column-name="{{ $column->getName() }}">{!! $columnRenderers[$column->getName()]->renderHeader() !!}</th>
        @endforeach
        @if($rowActionButtons)
            <th class="dms-row-action-column"><span class="pull-right">Actions</span></th>
        @endif
    </tr>
    </thead>
    @foreach($sections as $section)
        @if ($section->hasGroupData())
            <thead>
            <tr>
                @foreach ($section->getGroupData()->getData() as $columnName => $value)
                    <td data-column-name="{{ $columnName }}">{!! $columnRenderers[$columnName]->render($value) !!}</td>
                @endforeach
                @if($rowActionButtons)
                    <td class="dms-row-action-column"><span class="pull-right">Actions</span></td>
                @endif
            </tr>
            </thead>
        @endif

        <tbody>
        @foreach ($section->getRows() as $row)
            <tr>
                @foreach ($row->getData() as $columnName => $value)
                    <td data-column-name="{{ $columnName }}">{!! $columnRenderers[$columnName]->render($value) !!}</td>
                @endforeach
                @if($rowActionButtons)
                    <?php $objectId = $row->getCellComponentData(\Dms\Core\Common\Crud\IReadModule::SUMMARY_TABLE_ID_COLUMN) ?>
                    <td class="dms-row-action-column">
                        <div class="dms-row-button-control pull-right">
                            @if(isset($rowActionButtons['details']))
                                <a href="{{ $rowActionButtons['details']->getUrl($objectId) }}" title="View Details"
                                   class="btn btn-xs btn-info">
                                    <i class="fa fa-bars"></i>
                                </a>
                            @endif
                            @if(isset($rowActionButtons['edit']))
                                <a href="{{ $rowActionButtons['edit']->getUrl($objectId) }}" title="Edit"
                                   class="btn btn-xs btn-primary">
                                    <i class="fa fa-pencil-square-o"></i>
                                </a>
                            @endif
                            @if(isset($rowActionButtons['remove']))
                                <form class="dms-run-action-form inline"
                                      action="{{ $rowActionButtons['remove']->getUrl($objectId) }}"
                                      data-after-run-remove-closest="tr"
                                      method="post">
                                    {!! csrf_field() !!}
                                    <button type="submit" class="btn btn-xs btn-danger">
                                        <i class="fa fa-trash-o"></i>
                                    </button>
                                </form>
                            @endif

                            @if(array_diff_key($rowActionButtons, ['details' => true, 'edit' => true, 'remove' => true]))
                                <div class="inline" style="position: relative">
                                    <button type="button" class="btn btn-xs btn-default dropdown-toggle"
                                            data-toggle="dropdown"
                                            aria-expanded="false">
                                        &nbsp;<span class="fa fa-caret-down"></span>&nbsp;
                                    </button>
                                    <ul class="dropdown-menu  dropdown-menu-right">
                                        @foreach(array_diff_key($rowActionButtons, ['details' => true, 'edit' => true, 'remove' => true]) as $action)
                                            <li>
                                                @if($action->isPost())
                                                    <form class="dms-run-action-form inline"
                                                          action="{{ $action->getUrl($objectId) }}"
                                                          method="post">
                                                        {!! csrf_field() !!}
                                                        <button type="submit">{{ $action->getLabel() }}</button>
                                                    </form>
                                                @else
                                                    <a href="{{ $action->getUrl($objectId) }}">{{ $action->getLabel() }}</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    @endforeach
</table>