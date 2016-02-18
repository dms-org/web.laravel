Dms.table.initializeCallbacks.push(function (element) {

    element.find('.dms-table-control').each(function () {
        var control = $(this);
        var tableContainer = control.find('.dms-table-container');
        var table = tableContainer.find('table.dms-table');
        var filterForm = control.find('.dms-table-quick-filter-form');
        var loadRowsUrl = control.attr('data-load-rows-url');
        var reorderRowsUrl = control.attr('data-reorder-row-action-url');

        var currentPage = 0;

        var getItemsPerPage = function () {
            return filterForm.find('select[name=items_per_page]').val()
        };

        var criteria = {
            orderings: [],
            conditions: []
        };

        var currentAjaxRequest;

        var loadCurrentPage = function () {
            tableContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            criteria.offset = currentPage * getItemsPerPage();
            criteria.max_rows = getItemsPerPage();

            currentAjaxRequest = $.ajax({
                url: loadRowsUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (tableData) {
                table.html(tableData);
                Dms.table.initialize(tableContainer);
            });

            currentAjaxRequest.fail(function () {
                tableContainer.addClass('error');

                swal({
                    title: "Could not load table data",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                tableContainer.removeClass('loading');
            });
        };

        filterForm.find('button').click(function () {
            criteria.orderings = [
                {
                    component: filterForm.find('[name=component]').val(),
                    direction: filterForm.find('[name=direction]').val()
                }
            ];

            criteria.conditions = [
                // TODO:
            ];

            loadCurrentPage();
        });

        loadCurrentPage();
    });
});