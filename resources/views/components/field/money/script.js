Dms.form.initializeCallbacks.push(function (element) {
    element.find('input.dms-money-input').each(function () {
        $(this).attr({
            'type': $(this).attr('step') ? 'number' : 'text',
            'data-parsley-type': 'number'
        });
    });
});