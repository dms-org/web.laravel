<div class="dms-widget dms-widget-unparameterized-action" data-action-label="{{ $action->getLabel() }}">
    <button class="btn btn-{{ $class or 'default' }}" data-run-action-url="{{ $actionUrl }}">{{ $action->getLabel() }} <i class="fa fa-arrow-right"></i></button>
</div>