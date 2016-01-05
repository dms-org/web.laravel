Dms.form.initializeCallbacks.push(function (element) {
    element.find('input.dms-colour-input').each(function () {
        var config = {
            showInput: true,
            showPalette: true
        };

        if ($(this).hasClass('dms-colour-input-rgb')) {
            config.preferredFormat = 'rgb';
        } else if ($(this).hasClass('dms-colour-input-rgba')) {
            config.preferredFormat = 'rgba';
        }

        $(this).spectrum(config);
    });
});