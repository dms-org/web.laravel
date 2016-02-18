window.Dms = {
    config: {},
    global: {
        initialize: function (element) {
            $.each(Dms.global.initializeCallbacks, function (index, callback) {
                callback(element);
            });
        },
        initializeCallbacks: []
    },
    form: {
        initialize: function (element) {
            var callbacks = Dms.form.initializeCallbacks.concat(Dms.form.initializeValidationCallbacks);

            $.each(callbacks, function (index, callback) {
                callback(element);
            });
        },
        validation: {}, // @see ./form-validation.js
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
    utilities: {} // @see ./utilities.js
};

$(document).ready(function () {
    Dms.global.initialize($(document));
    Dms.form.initialize($(document));
    Dms.table.initialize($(document));
    Dms.chart.initialize($(document));
    Dms.widget.initialize($(document));
});