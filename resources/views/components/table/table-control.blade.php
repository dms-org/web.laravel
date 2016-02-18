<?php /** @var \Dms\Core\Table\ITableStructure $structure */ ?>
<?php /** @var \Dms\Core\Module\ITableView $table */ ?>
<div
        class="dms-table-control"
        data-load-rows-url="{{ $loadRowsUrl }}"
        @if ($reorderRowActionUrl)
        data-reorder-row-action-url="{{ $reorderRowActionUrl }}"
        @endif
>
    <div class="row dms-table-quick-filter-form form-inline">

        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-addon">Items Per Page</span>

                <div class="form-group">
                    <select name="items_per_page" class="form-control">
                        @foreach([25, 50, 100, 200, 1000] as $amount)
                            <option value="{{ $amount }}">{{ $amount }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-addon">Order By</span>

                <div class="form-group">
                    <select name="component" class="form-control">
                        @foreach($structure->getColumns() as $column)
                            @foreach($column->getComponents() as $component)
                                <option value="{{ $column->getName() . '.' . $component->getName() }}">
                                    {{ $column->getLabel() . ' > ' . $component->getLabel() }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <select name="direction" class="form-control">
                        <option value="{{ \Dms\Core\Model\Criteria\OrderingDirection::ASC }}">
                            Asc
                        </option>
                        <option value="{{ \Dms\Core\Model\Criteria\OrderingDirection::DESC }}">
                            Desc
                        </option>
                    </select>
                </div>

                <span class="input-group-addon">Filter</span>

                <div class="form-group">
                    <input name="filter" class="form-control" type="text" placeholder="Filter"/>
                </div>

                <span class="input-group-btn">
                    <button class="btn btn-info" type="button"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </div>

        <div class="col-md-12">
            <hr/>
        </div>

        <div class="dms-table-container row">
            <div class="col-xs-12">
                <table class="dms-table table"></table>
            </div>
        </div>
    </div>
</div>