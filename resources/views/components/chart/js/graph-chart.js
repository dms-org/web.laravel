Dms.chart.initializeCallbacks.push(function () {
    $('.dms-chart.dms-graph-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var chartType = !!chart.attr('data-chart-type');
        var horizontalAxisKey = chart.attr('data-horizontal-axis-key');
        var verticalAxisKeys = JSON.parse(chart.attr('data-vertical-axis-keys'));
        var verticalAxisLabels = JSON.parse(chart.attr('data-vertical-axis-labels'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.guidGenerator());
        }

        var morrisConfig = {
            element: chart.attr('id'),
            data: chartData,
            xkey: horizontalAxisKey,
            ykeys: verticalAxisKeys,
            labels: verticalAxisLabels
        };

        if (chartType === 'bar') {
            Morris.Bar(morrisConfig);
        } else if (chartType === 'area') {
            Morris.Area(morrisConfig);
        } else {
            Morris.Line(morrisConfig);
        }
    });
});