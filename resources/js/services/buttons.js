Dms.global.initializeCallbacks.push(function (element) {
    element.find('button[data-a-href]').css('cursor', 'pointer');

    element.delegate('button[data-a-href]', 'click', function () {
        var button = $(this);
        var link = $('<a/>').attr('href', $(this).attr('data-a-href')).hide();
        button.before(link);
        link.click();
    });

    element.find('.btn.btn-active-toggle').on('click', function () {
       $(this).toggleClass('active');
    });
});