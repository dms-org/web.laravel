Dms.global.initializeCallbacks.push(function (element) {
    element.find('button[data-a-href]').css('cursor', 'pointer');

    element.delegate('button[data-a-href]', 'click', function () {
        var button = $(this);
        var link = $('<a/>')
            .attr('href', $(this).attr('data-a-href'))
            .addClass('dms-placeholder-a')
            .hide();
        button.before(link);
        link.click();
    });

    element.delegate('a[href].dms-placeholder-a', 'click', function () {
        window.location.href = $(this).attr('href');
    });

    element.find('.btn.btn-active-toggle').on('click', function () {
       $(this).toggleClass('active');
    });
});