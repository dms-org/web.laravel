<?php /** @var \Dms\Core\Module\IAction $action */ ?>
<?php /** @var \Dms\Core\Form\IStagedForm $stagedForm */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Form\FormRenderer $formRenderer */ ?>
<form
        action="{{ route('dms::package.module.action.run', [$packageName, $moduleName, $actionName]) }}"
        method="post"
        enctype="multipart/form-data"
        class="dms-staged-form form-horizontal"
>
    {!! csrf_field() !!}

    <?php $stageNumber = 1 ?>
    @foreach ($stagedForm->getAllStages() as $stage)
    @if ($stage instanceof \Dms\Core\Form\Stage\IndependentFormStage)
    <div class="dms-form-stage loaded">
        {!!  $formRenderer->renderFields($stage->loadForm()) !!}
    </div>
    @else
    <div
            class="dms-form-stage"
            data-load-stage-url="{{ route('dms::package.module.action.form.stage', [$packageName, $moduleName, $actionName, $stageNumber]) }}"
            @if($stage->getRequiredFieldNames() !== null) data-stage-dependent-fields="{{ json_encode($stage->getRequiredFieldNames()) }}" @endif
    >

    </div>
    @endif
    <?php $stageNumber++ ?>
    @endforeach

    <button class="btn btn-{{ $submitButtonClass or 'default' }}" type="submit">{{ $action->getName() }} <i class="fa fa-arrow-right"></i></button>
</form>