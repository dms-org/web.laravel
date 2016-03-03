Dms.form.initializeCallbacks.push(function (element) {
    element.find('.dms-inner-module').each(function () {
        var innerModule = $(this);
        var currentValue = JSON.parse(innerModule.attr('data-value') || '[]');

        if (innerModule.attr('data-readonly')) {
            innerModule.find(':input').attr('readonly', 'readonly');
        }
    });
});