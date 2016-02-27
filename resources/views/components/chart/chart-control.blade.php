<?php /** @var \Dms\Core\Module\IChartView $chart */ ?>
<div
        class="dms-chart-control"
        data-load-chart-url="{{ $loadChartDataUrl }}"
>
    <div class="dms-chart-container">
        <div class="dms-chart"></div>
        @include('dms::partials.spinner')
    </div>
</div>