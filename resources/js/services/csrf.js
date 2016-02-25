Dms.utilities.getCsrfHeaders = function () {
    return {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };
};

Dms.global.initializeCallbacks.push(function () {
    $.ajaxSetup({
        headers: Dms.utilities.getCsrfHeaders()
    });
});