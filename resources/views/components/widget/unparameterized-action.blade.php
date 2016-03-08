<div class="dms-widget dms-widget-unparameterized-action" data-action-label="{{ \Dms\Web\Laravel\Util\StringHumanizer::title($action->getName()) }}">
    <p>
        <button class="dms-run-action-form btn btn-{{ $class or 'default' }}" data-action="{{ $actionUrl }}" data-method="post">
            {{ \Dms\Web\Laravel\Util\StringHumanizer::title($action->getName()) }} <i class="fa fa-arrow-right"></i>
        </button>
    </p>
</div>