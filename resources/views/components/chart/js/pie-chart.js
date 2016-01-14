Dms.chart.initializeCallbacks.push(function () {
    $('.dms-chart.dms-pie-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.guidGenerator());
        }

        Morris.Donut({
            element: chart.attr('id'),
            data: chartData
        });
    });
});