Dms.global.initializeCallbacks.push(function () {
    $('button[data-a-href]').css('cursor', 'pointer').click(function () {
        window.location.href = $(this).attr('data-a-href');
    });
});