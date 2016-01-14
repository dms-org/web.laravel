<?php

namespace Dms\Web\Laravel\Renderer\Table;

use Dms\Core\Table\IDataTable;

/**
 * The table renderer class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TableRenderer
{
    /**
     * @var ColumnRendererFactoryCollection
     */
    protected $columnRendererFactories;

    /**
     * TableRenderer constructor.
     *
     * @param ColumnRendererFactoryCollection $columnRendererFactories
     */
    public function __construct(ColumnRendererFactoryCollection $columnRendererFactories)
    {
        $this->columnRendererFactories = $columnRendererFactories;
    }

    /**
     * Renders the supplied data table as a html string.
     *
     * @param IDataTable $table
     *
     * @return string
     */
    public function renderTable(IDataTable $table)
    {
        $columnRenderers = [];

        foreach ($table->getStructure()->getColumns() as $column) {
            $columnRenderers[$column->getName()] = $this->columnRendererFactories->buildRendererFor($column);
        }

        return (string)view('dms::components.table.data-table')
                ->with([
                        'columnRenderers' => $columnRenderers,
                        'sections'        => $table->getSections(),
                ]);
    }
}