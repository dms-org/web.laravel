Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form, form.dms-run-action-form').each(function () {
        var form = $(this);
        var parsley = form.parsley(window.ParsleyConfig);
        var afterRunCallbacks = [];
        var submitButtons = form.find('input[type=submit], button[type=submit]');
        var submitMethod = form.attr('method');
        var submitUrl = form.attr('action');

        var isFormValid = function () {
            return parsley.isValid()
                && form.find('.dms-validation-message *').length === 0
                && form.find('.dms-form-stage-container').length === form.find('.dms-form-stage-container.loaded').length;
        };

        submitButtons.on('click before-confirmation', function (e) {
            parsley.validate();

            if (!isFormValid()) {
                e.stopImmediatePropagation();
                return false;
            }
        });

        form.on('submit', function (e) {
            e.preventDefault();

            Dms.form.validation.clearMessages(form);

            var fieldsToReappend = [];
            form.find('.dms-form-no-submit').each(function () {
                var removedFields = $(this).children().detach();

                fieldsToReappend.push({
                    parentElement: $(this),
                    children: removedFields
                });
            });

            var formData = Dms.ajax.createFormData(form.get(0));

            $.each(fieldsToReappend, function (index, elements) {
                elements.parentElement.append(elements.children);
            });

            submitButtons.prop('disabled', true);
            submitButtons.addClass('ladda-button').attr('data-style', 'expand-right');
            var ladda = Ladda.create(submitButtons.get(0));
            ladda.start();

            var currentAjaxRequest = Dms.ajax.createRequest({
                url: submitUrl,
                type: submitMethod,
                processData: false,
                contentType: false,
                dataType: 'json',
                data: formData,
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();

                    if (form.find('input[type=file]').length && xhr.upload) {
                        xhr.upload.addEventListener('progress', function (event) {
                            if (event.lengthComputable) {
                                ladda.setProgress(event.loaded / event.total);
                            }
                        }, false);
                    }

                    return xhr;
                }
            });

            currentAjaxRequest.done(function (data) {
                Dms.action.responseHandler(data);
                $.each(afterRunCallbacks, function (index, callback) {
                    callback(data);
                });
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

                    default: // Unknown error
                        swal({
                            title: "Could not submit form",
                            text: "An unexpected error occurred",
                            type: "error"
                        });
                        break;
                }
            });

            currentAjaxRequest.always(function () {
                submitButtons.prop('disabled', false);
                ladda.stop();
            });

            return false;
        });

        var parentToRemove = form.attr('data-after-run-remove-closest');
        if (parentToRemove) {
            afterRunCallbacks.push(function () {
                form.closest(parentToRemove).fadeOut(100);
            });
        }

        afterRunCallbacks.push(function () {
            form.find('input[type=password]').val('');
        });
    });
});