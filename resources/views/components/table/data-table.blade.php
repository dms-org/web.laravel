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
            <th class="dms-row-action-column">Actions</th>
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
                    <td class="dms-row-action-column">Actions</td>
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
                    <td class="dms-row-action-column">
                        @foreach($rowActionButtons as $action)
                            @if($action->isPost())
                                <form class="dms-run-action-form inline" action="{{ $action->getUrl($row) }}" method="post">
                                    {!! csrf_field() !!}
                                    <button type="submit" class="btn btn-xs btn-default">{{ $action->getLabel() }}</button>
                                </form>
                            @else
                                <a class="btn btn-xs btn-default" href="{{ $action->getUrl($row) }}">{{ $action->getLabel() }}</a>
                            @endif
                        @endforeach
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    @endforeach
</table>