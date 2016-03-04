window.Dms = {
    config: {
        // @see /resources/views/partials/js-config.blade.php
    },
    global: {
        initialize: function (element) {
            $.each(Dms.global.initializeCallbacks, function (index, callback) {
                callback(element);
            });
        },
        initializeCallbacks: []
    },
    action: {
        responseHandler: null // @see ./services/action-response.js
    },
    alerts: {
        add: null // @see ./services/alerts.js
    },
    ajax: {
        interceptors: []
        // @see ./services/ajax.js
    },
    form: {
        initialize: function (element) {
            var callbacks = Dms.form.initializeCallbacks.concat(Dms.form.initializeValidationCallbacks);

            $.each(callbacks, function (index, callback) {
                callback(element);
            });
        },
        stages: {}, // @see ./services/form-stages.js
        validation: {}, // @see ./services/validation/form-validation.js
        initializeCallbacks: [],
        initializeValidationCallbacks: []
    },
    table: {
        initialize: function (element) {
            $.each(Dms.table.initializeCallbacks, function (index, callback) {
                callback(element);
            });
        },
        initializeCallbacks: []
    },
    chart: {
        initialize: function (element) {
            $.each(Dms.chart.initializeCallbacks, function (index, callback) {
                callback(element);
            });
        },
        initializeCallbacks: []
    },
    widget: {
        initialize: function (element) {
            $.each(Dms.widget.initializeCallbacks, function (index, callback) {
                callback(element);
            });
        },
        initializeCallbacks: []
    },
    utilities: {} // @see ./services/utilities.js
};

$(document).ready(function () {
    Dms.global.initialize($(document));
    Dms.form.initialize($(document));
    Dms.table.initialize($(document));
    Dms.chart.initialize($(document));
    Dms.widget.initialize($(document));
});