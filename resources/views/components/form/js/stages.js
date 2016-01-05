Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        var stageElements = form.find('.dms-form-stage');

        stageElements.each(function () {
            var currentStage = $(this);
            var previousStages = currentStage.prevAll('.dms-form-stage');
            var loadStageUrl = currentStage.attr('data-stage-load-url');
            var dependentFields = currentStage.attr('data-stage-dependent-fields');
            var dependentFieldsSelector = null;
            var currentAjaxRequest = null;

            if (dependentFields) {
                var dependentFieldNames = JSON.parse(dependentFields);

                var selectors = [];
                $.each(dependentFieldNames, function (index, fieldName) {
                    selectors.push('*[name="' + fieldName + '"]:input');
                    selectors.push('*[name^="' + fieldName + '["][name$="]"]:input');
                });

                dependentFieldsSelector = selectors.join(',');
            } else {
                dependentFieldsSelector = '*[name]:input';
            }

            previousStages.on('change input', dependentFieldsSelector, function () {
                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                }

                currentStage.removeClass('loaded');
                currentStage.addClass('loading');

                var formData = new FormData();

                previousStages.find(dependentFieldsSelector).each(function () {
                    var fieldName = $(this).attr('name');

                    if ($(this).is('[type=file]')) {
                        if ($(this).is('[multiple]')) {
                            $.each(this.files, function (index, file) {
                                formData.append(fieldName, file);
                            });
                        } else {
                            formData.append(fieldName, this.files[0]);
                        }
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
                    currentStage.removeClass('loading');
                    currentStage.addClass('loaded');
                    Dms.form.validation.clearMessages(form);
                    currentStage.html(html);
                });

                currentAjaxRequest.fail(function (xhr) {
                    switch (xhr.status) {
                        case 422: // Unprocessable  Entity (validation failure)
                            var validation = JSON.parse(xhr.responseText);
                            Dms.form.validation.displayMessages(form, validation.fields, validation.constraints);
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
            });
        });
    });
});