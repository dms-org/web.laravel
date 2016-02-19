Dms.alerts.add = function (type, title, message) {
    var alertsList = $('.alerts-list');
    var templates = alertsList.find('.alert-templates');


    var alert = templates.find('.alert.alert-' + type).clone(true);
    alert.find('.alert-title').text(title);
    alert.find('.alert-message').text(message);

    alertsList.append(alert);
};