Dms.chart.initializeCallbacks.push(function (element) {

    element.find('.dms-chart-control').each(function () {
        var control = $(this);
        var chartContainer = control.find('chart.dms-chart-container');
        var loadChartUrl = control.attr('data-load-chart-url');


        var criteria = {
            orderings: [],
            conditions: []
        };

        var currentAjaxRequest;

        var loadCurrentData = function () {
            chartContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            currentAjaxRequest = $.ajax({
                url: loadChartUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (chartData) {
                chartContainer.html(chartData);
                Dms.chart.initialize(chartContainer);
            });

            currentAjaxRequest.fail(function () {
                chartContainer.addClass('error');

                swal({
                    title: "Could not load chart data",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                chartContainer.removeClass('loading');
            });
        };

        loadCurrentData();
    });
});