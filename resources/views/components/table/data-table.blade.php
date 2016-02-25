<?php /** @var \Dms\Core\Table\IColumn[] $columns */ ?>
<?php /** @var \Dms\Core\Table\ITableSection[] $sections */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\IColumnRenderer[] $columnRenderers */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Action\ActionButton[] $rowActionButtons */ ?>
<?php /** @var bool $allowsReorder */ ?>
<table class="table dms-table">
    @if (!$sections || !$sections[0]->hasGroupData())
        <thead>
        <tr>
            @foreach ($columns as $column)
                <th data-column-name="{{ $column->getName() }}"
                    @if($column->isHidden()) class="hidden" @endif>{!! $columnRenderers[$column->getName()]->renderHeader() !!}</th>
            @endforeach
            @if($rowActionButtons)
                <th class="dms-row-action-column"><span class="pull-right">Actions</span></th>
            @endif
        </tr>
        </thead>
    @endif
    @forelse($sections as $section)
        @if ($section->hasGroupData())
            <?php $groupData = $section->getGroupData()->getData()?>
            <thead>
            <tr>
                <td colspan="{{ count($columns) + ($rowActionButtons || $allowsReorder ? 1 : 0) }}">
                    @foreach ($groupData as $columnName => $value)
                        <h4>
                            {{ $columns[$columnName]->getLabel() }}
                            : {!! $columnRenderers[$columnName]->render($value) !!}
                        </h4>
                    @endforeach
                </td>
            </tr>
            <tr>
                @foreach ($columns as $columnName => $column)
                    @unless($groupData[$columnName] ?? false)
                        <th data-column-name="{{ $column->getName() }}" @if($column->isHidden()) class="hidden" @endif>
                            {!! $columnRenderers[$column->getName()]->renderHeader() !!}
                        </th>
                    @endunless
                @endforeach
                @if($rowActionButtons)
                    <th class="dms-row-action-column"><span class="pull-right">Actions</span></th>
                @endif
            </tr>
            </thead>
        @endif

        <tbody class="@if($allowsReorder) dms-table-body-sortable @endif">
        @foreach ($section->getRows() as $row)
            <tr>
                @foreach ($row->getData() as $columnName => $value)
                    @unless($groupData[$columnName] ?? false)
                        <td data-column-name="{{ $columnName }}" @if($columns[$columnName]->isHidden()) class="hidden" @endif>
                            {!! $columnRenderers[$columnName]->render($value) !!}
                        </td>
                    @endunless
                @endforeach
                @if($rowActionButtons || $allowsReorder)
                    <?php $objectId = $row->getCellComponentData(\Dms\Core\Common\Crud\IReadModule::SUMMARY_TABLE_ID_COLUMN) ?>
                    <td class="dms-row-action-column" data-object-id="{{ $objectId }}">
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
                                <div class="inline dropdown-container">
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

                            @if ($allowsReorder)
                                <button title="Reorder" class="btn btn-xs btn-success dms-drag-handle">
                                    <i class="fa fa-arrows"></i>
                                </button>
                            @endif
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    @empty
        <tbody>
        <tr>
            <td colspan="{{ count($columns) + ($rowActionButtons || $allowsReorder ? 1 : 0) }}">
                <div class="help-block text-center">There are no items</div>
            </td>
        </tr>
        </tbody>
    @endforelse
</table>