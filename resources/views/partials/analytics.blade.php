<script>
    (function (w, d, s, g, js, fs) {
        g = w.gapi || (w.gapi = {});
        g.analytics = {
            q: [], ready: function (f) {
                this.q.push(f);
            }
        };
        js = d.createElement(s);
        fs = d.getElementsByTagName(s)[0];
        js.src = 'https://apis.google.com/js/platform.js';
        fs.parentNode.insertBefore(js, fs);
        js.onload = function () {
            g.load('analytics');
        };
    }(window, document, 'script'));
</script>

<div class="panel panel-default">
    <div class="panel-heading">Analytics</div>
    <div class="panel-body">
        <div id="embed-api-auth-container"></div>
        <div id="chart-container"></div>
        <div id="breakdown-chart-container"></div>
    </div>
</div>

<script>

    gapi.analytics.ready(function () {

        /**
         * Authorize the user immediately if the user has already granted access.
         * If no access has been created, render an authorize button inside the
         * element with the ID "embed-api-auth-container".
         */
        gapi.analytics.auth.authorize({
            container: 'embed-api-auth-container',
            clientid: '{{ config('dms.analytics.google.client-id') }}'
        });

        var siteId = <?= json_encode(config('dms.analytics.google.site-id')) ?>;

        /**
         * Create a new DataChart instance with the given query parameters
         * and Google chart options. It will be rendered inside an element
         * with the id "chart-container".
         */
        var dataChart = new gapi.analytics.googleCharts.DataChart({
            query: {
                ids: siteId,
                metrics: 'ga:sessions',
                dimensions: 'ga:date',
                'start-date': '30daysAgo',
                'end-date': 'yesterday'
            },
            chart: {
                container: 'chart-container',
                type: 'LINE',
                options: {
                    width: '100%'
                }
            }
        });


        /**
         * Create a table chart showing top browsers for users to interact with.
         * Clicking on a row in the table will update a second timeline chart with
         * data from the selected browser.
         */
        var mainChart = new gapi.analytics.googleCharts.DataChart({
            query: {
                ids: siteId,
                'dimensions': 'ga:browser',
                'metrics': 'ga:sessions',
                'sort': '-ga:sessions',
                'max-results': '6'
            },
            chart: {
                type: 'TABLE',
                container: 'breakdown-chart-container',
                options: {
                    width: '100%'
                }
            }
        });

        dataChart.execute();
        mainChart.execute();
    });
</script>