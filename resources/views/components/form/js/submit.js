Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form, form.dms-run-action-form').each(function () {
        var form = $(this);
        var afterRunCallbacks = [];
        var submitButtons = form.find('input[type=submit], button[type=submit]');
        var submitMethod = form.attr('method');
        var submitUrl = form.attr('action');

        form.on('submit', function (e) {
            e.preventDefault();

            Dms.form.validation.clearMessages(form);

            var formData = new FormData(form.get(0));

            submitButtons.prop('disabled', true);
            submitButtons.addClass('ladda-button').attr('data-style', 'expand-right');
            var ladda = Ladda.create(submitButtons.get(0));
            ladda.start();

            var currentAjaxRequest = $.ajax({
                url: submitUrl,
                type: submitMethod,
                processData: false,
                contentType: false,
                dataType: 'json',
                data: formData,
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();

                    if(xhr.upload){
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