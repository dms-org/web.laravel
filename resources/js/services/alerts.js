Dms.alerts.add = function (type, title, message) {
    var alertsList = $('.alerts-list');
    var templates = alertsList.find('.alert-templates');


    var alert = templates.find('.alert.alert-' + type).clone(true);

    if (!message) {
        var typeTitle = type.charAt(0).toUpperCase() + type.slice(1);

        alert.find('.alert-title').text(typeTitle);
        alert.find('.alert-message').text(title);
    } else {
        alert.find('.alert-title').text(title);
        alert.find('.alert-message').text(message);
    }

    alertsList.append(alert.hide());
    alert.fadeIn();
};

Dms.global.initializeCallbacks.push(function () {
    var successFlash = Cookies.get('dms-flash-alert-success');

    if (successFlash) {
        Cookies.remove('dms-flash-alert-success');

        Dms.alerts.add('success', successFlash);
    }
});