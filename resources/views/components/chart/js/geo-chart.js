Dms.chart.initializeCallbacks.push(function (element) {
    element.find('.dms-geo-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var valueLabel = chart.attr('data-value-label');

        google.charts.load('current', {'packages': ['geochart']});
        google.charts.setOnLoadCallback(function() {
            var transformedChartData = [['Country', valueLabel]];

            $.each(chartData, function (index, row) {
                transformedChartData.push([row.label, row.value]);
            });

            var data = google.visualization.arrayToDataTable(transformedChartData);

            var googleChart = new google.visualization.GeoChart(chart.get(0));

            googleChart.draw(data, {});
        });
    });
});