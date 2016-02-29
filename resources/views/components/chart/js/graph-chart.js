Dms.chart.initializeCallbacks.push(function (element) {
    element.find('.dms-graph-chart').each(function () {
        var chart = $(this);
        var dateFormat = Dms.utilities.convertPhpDateFormatToMomentFormat(chart.attr('data-date-format'));
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var chartType = chart.attr('data-chart-type');
        var horizontalAxisKey = chart.attr('data-horizontal-axis-key');
        var verticalAxisKeys = JSON.parse(chart.attr('data-vertical-axis-keys'));
        var verticalAxisLabels = JSON.parse(chart.attr('data-vertical-axis-labels'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.idGenerator());
        }

        $.each(chartData, function (index, row) {
            row[horizontalAxisKey] = moment(row[horizontalAxisKey], dateFormat).valueOf();
        });

        var morrisConfig = {
            element: chart.attr('id'),
            data: chartData,
            xkey: horizontalAxisKey,
            ykeys: verticalAxisKeys,
            labels: verticalAxisLabels,
            resize: true,
            redraw: true,
            dateFormat: function (timestamp) {
                return moment(timestamp).format(dateFormat);
            }
        };

        var morrisChart;
        if (chartType === 'bar') {
            morrisChart = Morris.Bar(morrisConfig);
        } else if (chartType === 'area') {
            morrisChart = Morris.Area(morrisConfig);
        } else {
            morrisChart = Morris.Line(morrisConfig);
        }

        $(window).on('resize', function () {
            if (morrisChart.raphael) {
                morrisChart.redraw();
            }
        });
    });
});