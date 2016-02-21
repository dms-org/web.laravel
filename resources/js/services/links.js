Dms.global.initializeCallbacks.push(function () {
    $('a').click(function (e) {
        if ($(this).attr('disabled')) {
            e.stopImmediatePropagation();

            return false;
        }

        return true;
    });
});