Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        var parsley = form.parsley(window.ParsleyConfig);
        var stageElements = form.find('.dms-form-stage');

        var arePreviousFieldsValid = function (fields) {
            parsley.validate();

            return fields.closest('.form-group').find('.dms-validation-message *').length === 0;
        };

        stageElements.each(function () {
            var currentStage = $(this);

            if (currentStage.is('.loaded')) {
                return;
            }

            var container = currentStage.closest('.dms-form-stage-container');
            var previousStages = container.prevAll('.dms-form-stage-container').find('.dms-form-stage');
            var loadStageUrl = currentStage.attr('data-load-stage-url');
            var dependentFields = currentStage.attr('data-stage-dependent-fields');
            var currentAjaxRequest = null;

            var makeDependentFieldSelectorFor = function (selector) {
                if (dependentFields) {
                    var dependentFieldNames = JSON.parse(dependentFields);

                    var selectors = [];
                    $.each(dependentFieldNames, function (index, fieldName) {
                        selectors.push(selector + '[name="' + fieldName + '"]:input');
                        selectors.push(selector + '[name^="' + fieldName + '["][name$="]"]:input');
                    });

                    return selectors.join(',');
                } else {
                    return selector + '[name]:input';
                }
            };

            var loadNextStage = function () {
                var previousFields = previousStages.find(makeDependentFieldSelectorFor('*'));

                if (!arePreviousFieldsValid(previousFields)) {
                    return;
                }

                Dms.form.validation.clearMessages(form);

                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                }

                container.removeClass('loaded');
                container.addClass('loading');

                var formData = new FormData();

                previousFields.each(function () {
                    var fieldName = $(this).attr('name');

                    if ($(this).is('[type=file]')) {
                        $.each(this.files, function (index, file) {
                            formData.append(fieldName, file);
                        });
                    } else {
                        formData.append(fieldName, $(this).val());
                    }
                });

                currentAjaxRequest = $.ajax({
                    url: loadStageUrl,
                    type: 'post',
                    processData: false,
                    contentType: false,
                    dataType: 'html',
                    data: formData
                });

                currentAjaxRequest.done(function (html) {
                    container.addClass('loaded');
                    currentStage.html(html);
                    Dms.form.initialize(currentStage);
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
            previousStages.on('change', makeDependentFieldSelectorFor('select'), loadNextStage);
        });
    });
});