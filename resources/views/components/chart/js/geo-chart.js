Dms.chart.initializeCallbacks.push(function (element) {
    element.find('.dms-geo-chart').each(function () {
        var chart = $(this);
        var isCityChart = chart.attr('data-city-chart');
        var hasLatLng = chart.attr('data-has-lat-lng');
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var region = chart.attr('data-region');
        var locationLabel = chart.attr('data-location-label');
        var valueLabels = JSON.parse(chart.attr('data-value-labels'));

        google.charts.load('current', {'packages': ['geochart']});
        google.charts.setOnLoadCallback(function () {
            var headers = [];

            if (hasLatLng) {
                headers.push('Latitude');
                headers.push('Longitude');
            }

            headers.push(locationLabel);
            headers = headers.concat(valueLabels);

            var transformedChartData = [headers];

            $.each(chartData, function (index, row) {
                transformedChartData.push((hasLatLng ?  row.lat_lng : []).concat([row.label]).concat(row.values));
            });

            var data = google.visualization.arrayToDataTable(transformedChartData);

            var googleChart = new google.visualization.GeoChart(chart.get(0));

            googleChart.draw(data, {
                displayMode: isCityChart ? 'markers' : 'regions',
                region: region
            });
        });
    });
});