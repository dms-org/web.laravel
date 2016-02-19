Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        var submitButtons = form.find('input[type=submit], button[type=submit]');
        var submitMethod = form.attr('method');
        var submitUrl = form.attr('action');

        form.on('submit', function (e) {
            e.preventDefault();

            Dms.form.validation.clearMessages(form);

            var formData = new FormData(form.get(0));

            submitButtons.prop('disabled', true);

            var currentAjaxRequest = $.ajax({
                url: submitUrl,
                type: submitMethod,
                processData: false,
                contentType: false,
                dataType: 'json',
                data: formData
            });

            currentAjaxRequest.done(function (data) {
                Dms.action.responseHandler(data);
            });

            currentAjaxRequest.fail(function (xhr) {
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
            });
        });
    });
});