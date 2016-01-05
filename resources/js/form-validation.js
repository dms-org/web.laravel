Dms.form.validation.clearMessages = function (form) {
    form.removeClass('has-error');
    form.find('.form-group').removeClass('has-error');
    form.find('.help-block.help-block-error').remove();
};

Dms.form.validation.displayMessages = function (form, fieldMessages, generalMessages) {
    if (!fieldMessages && !generalMessages) {
        return;
    }

    form.addClass('has-error');

    var makeHelpBlock = function () {
        return $('<span />').addClass(['help-block', 'help-block-error']);
    };

    var helpBlock = makeHelpBlock();

    $.each(generalMessages, function (index, message) {
        helpBlock.append($('<strong />').text(message));
    });

    form.prepend(helpBlock);

    var flattenedFieldMessages = {};

    var visitMessages = function (fieldName, messages) {
        if ($.isArray(messages)) {
            $.each(messages, function (index, message) {
                flattenedFieldMessages[fieldName] = message;
            });
        } else {
            $.each(messages.constraints, function (index, message) {
                flattenedFieldMessages[fieldName] = message;
            });

            $.each(messages.fields, function (fieldElementName, elementMessages) {
                visitMessages(fieldName + '[' + fieldElementName + ']', elementMessages);
            });
        }
    };
    $.each(fieldMessages, visitMessages);

    $.each(flattenedFieldMessages, function (fieldName, messages) {
        var fieldGroup = form.find('.form-group[data-field-name="' + fieldName + '"]');

        var helpBlock = makeHelpBlock();
        $.each(messages, function (index, message) {
            helpBlock.append($('<strong />').text(message));
        });

        fieldGroup.prepend(helpBlock);
    });
};