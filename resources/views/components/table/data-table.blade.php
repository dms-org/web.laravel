<?php /** @var \Dms\Core\Table\IColumn[] $columns */ ?>
<?php /** @var \Dms\Core\Table\ITableSection[] $sections */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Table\IColumnRenderer[] $columnRenderers */ ?>
<table class="table">
    <thead>
    <tr>
        @foreach ($columns as $column)
            <th>{!! $columnRenderers[$column->getName()]->renderHeader() !!}</th>
        @endforeach
    </tr>
    </thead>
    @foreach($sections as $section)
        @if ($section->hasGroupData())
            <thead>
            <tr>
                @foreach ($section->getGroupData()->getData() as $columnName => $value)
                    <td>{!! $columnRenderers[$columnName]->renderValue($value) !!}</td>
                @endforeach
            </tr>
            </thead>
        @endif

        <tbody>
        @foreach ($section->getRows() as $row)
            <tr>
                @foreach ($row->getData() as $columnName => $value)
                    <td>{!! $columnRenderers[$columnName]->renderValue($value) !!}</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    @endforeach
</table>