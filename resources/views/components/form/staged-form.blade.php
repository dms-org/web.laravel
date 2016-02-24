<?php /** @var \Dms\Core\Module\IAction $action */ ?>
<?php /** @var \Dms\Core\Form\IStagedForm $stagedForm */ ?>
<?php /** @var \Dms\Web\Laravel\Renderer\Form\FormRenderer $formRenderer */ ?>
<?php /** @var array $hiddenValues */ ?>
<form
        action="{{ route('dms::package.module.action.run', [$packageName, $moduleName, $actionName]) }}"
        method="post"
        enctype="multipart/form-data"
        class="dms-staged-form form-horizontal"
        novalidate
>
    {!! csrf_field() !!}

    @if($hiddenValues)
        <div class="dms-form-stage-container hidden">
            <div class="dms-form-stage loaded">
                @foreach($hiddenValues ?? [] as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}"/>
                @endforeach
            </div>
        </div>
    @endif

    <?php $stageNumber = $initialStageNumber ?? 1 ?>
    @foreach ($stagedForm->getAllStages() as $stage)
        @if ($stage instanceof \Dms\Core\Form\Stage\IndependentFormStage)
            <div class="dms-form-stage-container loaded">
                <div class="dms-form-stage">
                    {!!  $formRenderer->renderFields($stage->loadForm()) !!}
                </div>
            </div>
        @else

            <div class="dms-form-stage-container">
                <div
                        class="dms-form-stage"
                        data-load-stage-url="{{ route('dms::package.module.action.form.stage', [$packageName, $moduleName, $actionName, $stageNumber]) }}"
                        @if($stage->getRequiredFieldNames() !== null) data-stage-dependent-fields="{{ json_encode($stage->getRequiredFieldNames()) }}" @endif
                >
                    <div class="row">
                        <div class="col-lg-offset-2 col-lg-10 col-md-offset-3 col-md-9 col-sm-offset-4 col-sm-8">
                            <p class="help-block">
                                The following fields are not shown because they require you to enter the values for the previous fields in
                                this form.
                            </p>
                        </div>
                    </div>
                </div>
                @include('dms::partials.spinner')
            </div>
        @endif
        <?php $stageNumber++ ?>
    @endforeach

    <button class="btn btn-{{ \Dms\Web\Laravel\Util\KeywordTypeIdentifier::getClass($action->getName()) ?? 'primary' }}" type="submit">
        {{ \Dms\Web\Laravel\Util\StringHumanizer::title($action->getName()) }}
        <i class="fa fa-arrow-right"></i>
    </button>
</form>