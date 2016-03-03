Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        var parsley = form.parsley(window.ParsleyConfig);
        var stageElements = form.find('.dms-form-stage');

        var arePreviousFieldsValid = function (fields) {
            var originalScroll = $(document).scrollTop();
            var focusedElement = $(document.activeElement);
            parsley.validate();
            focusedElement.focus();
            $(document).scrollTop(originalScroll);

            return fields.closest('.form-group').find('.dms-validation-message *').length === 0;
        };

        stageElements.filter('.dms-dependent-form-stage').each(function () {
            var currentStage = $(this);
            var container = currentStage.closest('.dms-form-stage-container');
            var previousStages = container.prevAll('.dms-form-stage-container').find('.dms-form-stage');
            var loadStageUrl = currentStage.attr('data-load-stage-url');
            var dependentFields = currentStage.attr('data-stage-dependent-fields');
            var dependentFieldNames = dependentFields ? JSON.parse(dependentFields) : null;
            var currentAjaxRequest = null;
            var previousLoadAttempt = 0;
            var minMillisecondsBetweenLoads = 2000;
            var isWaitingForNextLoadAttempt = false;

            var makeDependentFieldSelectorFor = function (selector) {
                return Dms.form.stages.makeDependentFieldSelectorFor(dependentFieldNames, selector);
            };

            var loadNextStage = function () {

                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                }

                container.removeClass('loaded');
                container.addClass('loading');

                var currentTime = new Date().getTime();
                var millisecondsBetweenLastLoad = currentTime - previousLoadAttempt;

                if (millisecondsBetweenLastLoad >= minMillisecondsBetweenLoads) {
                    isWaitingForNextLoadAttempt = false;
                    previousLoadAttempt = currentTime;
                }
                else {
                    if (!isWaitingForNextLoadAttempt) {
                        isWaitingForNextLoadAttempt = true;
                        setTimeout(loadNextStage, minMillisecondsBetweenLoads - millisecondsBetweenLastLoad);
                    }
                    return;
                }

                var previousFields = previousStages.find(makeDependentFieldSelectorFor('*'));

                if (!arePreviousFieldsValid(previousFields)) {
                    container.removeClass('loading');
                    return;
                }

                Dms.form.validation.clearMessages(form);

                var formData = Dms.form.stages.createFormDataFromFields(previousFields);

                currentAjaxRequest = Dms.ajax.createRequest({
                    url: loadStageUrl,
                    type: 'post',
                    processData: false,
                    contentType: false,
                    dataType: 'html',
                    data: formData
                });

                currentAjaxRequest.done(function (html) {
                    container.addClass('loaded');
                    var currentValues = currentStage.getValues(true);
                    currentStage.html(html);
                    Dms.form.initialize(currentStage);
                    currentStage.restoreValues(currentValues);
                    form.triggerHandler('dms-form-updated');
                });

                currentAjaxRequest.fail(function (xhr) {
                    if (currentAjaxRequest.statusText === 'abort') {
                        return;
                    }

                    switch (xhr.status) {
                        case 422: // Unprocessable Entity (validation failure)
                            var validation = JSON.parse(xhr.responseText);
                            Dms.form.validation.displayMessages(form, validation.messages.fields, validation.messages.constraints);
                            break;

                        case 400: // Bad request
                            swal({
                                title: "Could not load form",
                                text: JSON.parse(xhr.responseText).message,
                                type: "error"
                            });
                            break;

                        default: // Unknown error
                            swal({
                                title: "Could not load form",
                                text: "An unexpected error occurred",
                                type: "error"
                            });
                            break;
                    }
                });

                currentAjaxRequest.always(function () {
                    container.removeClass('loading');
                });
            };

            previousStages.on('input', makeDependentFieldSelectorFor('input'), loadNextStage);
            previousStages.on('input', makeDependentFieldSelectorFor('textarea'), loadNextStage);
            previousStages.on('change', makeDependentFieldSelectorFor('select'), loadNextStage);

            if (dependentFieldNames) {
                var selectors = [];
                $.each(dependentFieldNames, function (index, fieldName) {
                    selectors.push('.form-group[data-field-name="' + fieldName + '"]');
                });

                previousStages.on('dms-change', selectors.join(','), loadNextStage);
            } else {
                previousStages.on('dms-change', '.form-group[data-field-name]', loadNextStage);
            }
        });
    });
});