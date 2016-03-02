Dms.form.initializeCallbacks.push(function (element) {
    element.find('.dms-inner-module').each(function () {
        var innerForm = $(this);

        if (innerForm.attr('data-readonly')) {
            innerForm.find(':input').attr('readonly', 'readonly');
        }
    });
});