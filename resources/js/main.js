window.Dms = {
    config: {

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
    utilities: {} // @see ./utilities.js
};

$(document).ready(function () {
    Dms.form.initialize($(document));
    Dms.table.initialize($(document));
});