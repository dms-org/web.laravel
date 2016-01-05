window.ParsleyValidator
    .addValidator('ip-address', {
        requirementType: 'string',
        validateString: function (value) {
            var ipV4Regex = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}$/;
            var ipV6Regex = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;

            if (!ipV4Regex.test(value)) {
                return true;
            }

            if (ipV4Regex.test(value)) {
                return true;
            }

            return false;
        },
        messages: {
            en: 'This value should be a valid ip address'
        }
    });

window.ParsleyValidator
    .addValidator('max-decimal-points', {
        requirementType: 'number',
        validateString: function (value, requirement) {
            return Dms.utilities.countDecimals(value) <= requirement;
        },
        messages: {
            en: 'This value should have a maximum of %d decimal places'
        }
    });
$(document).ready(function () {
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    $.widget.bridge('uibutton', $.ui.button);
});
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
    utilities: {}
};

$(document).ready(function () {
    Dms.form.initialize($(document));
    Dms.table.initialize($(document));
});
Dms.utilities.countDecimals = function (value) {
    if (value % 1 != 0) {
        return value.toString().split(".")[1].length;
    }

    return 0;
};

Dms.utilities.guidGenerator = function() {
    var S4 = function() {
        return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
}
Dms.form.initializeCallbacks.push(function (element) {

    var fieldCounter = 1;

    element.find('.dms-form-fieldset .form-group').each(function () {
        var fieldLabel = $(this).children('label[data-for]');
        var forFieldName = fieldLabel.attr('data-for');

        if (forFieldName) {
            var forField = $(this).first('*[name="' + forFieldName + '"]');

            if (!forField.attr('id')) {
                forField.attr('id', 'dms-field-' + fieldCounter);
                fieldCounter++;
            }

            fieldLabel.attr('for', forField.attr('id'));
        }
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        var amountOfStages = form.attr('data-amount-of-stages');
        var stageLoadUrl = form.attr('data-stage-load-url');

    });
});
Dms.form.initializeValidationCallbacks.push(function (element) {

    element.find('.dms-form-fields').each(function () {
        if (!$(this).attr('id')) {
            $(this).attr('id', Dms.utilities.guidGenerator());
        }
    });

    element.find('.dms-form-fields').each(function () {
        var formFieldsGroupId = $(this).attr('id');


        var buildElementSelect = function (fieldName) {
            return '#' + formFieldsGroupId + '*[type="' + fieldName + '"]:input';
        };

        var fieldValidations = {
            'data-equal-fields': 'data-parsley-equalto',
            'data-greater-than-fields': 'data-parsley-gt',
            'data-greater-than-or-equal-fields': 'data-parsley-gte',
            'data-less-than-fields': 'data-parsley-lt',
            'data-less-than-or-equal-fields': 'data-parsley-lte'
        };

        $.each(fieldValidations, function (validationAttr, parsleyAttr) {
            var fieldsMap = $(this).attr(validationAttr);

            if (fieldsMap) {
                $.each(JSON.parse(fieldsMap), function (fieldName, otherFieldName) {
                    $(this).find(buildElementSelect(fieldName)).attr(parsleyAttr, buildElementSelect(otherFieldName));
                });
            }
        });
    });

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        form.parsley();

        form.find('.dms-form-fields').each(function (index) {
            $(this).find(':input').attr('data-parsley-group', 'validation-group-' + index);
        });
    });

    element.find('form.dms-form').each(function () {
        $(this).parsley();
    });
});
Dms.form.initializeCallbacks.push(function (element) {

});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('.list-of-checkboxes').each(function () {
        var listOfCheckboxes = $(this);

        var minFields = listOfCheckboxes.attr('data-min-elements') || 0;
        var maxFields = listOfCheckboxes.attr('data-max-elements') || Infinity;

        listOfCheckboxes.find('input[type=checkbox]').on('click', function (e) {
            var currentCount = listOfCheckboxes.find('input[type=checkbox]:checked').length;

            if (currentCount >= maxFields && !$(this).is(':checked')) {
                e.preventDefault();
            }
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    var convertPhpDateFormatToMomentFormat = function (format) {
        var replacements = {
            'd': 'DD',
            'D': 'ddd',
            'j': 'D',
            'l': 'dddd',
            'N': 'E',
            'S': 'o',
            'w': 'e',
            'z': 'DDD',
            'W': 'W',
            'F': 'MMMM',
            'm': 'MM',
            'M': 'MMM',
            'n': 'M',
            'o': 'YYYY',
            'Y': 'YYYY',
            'y': 'YY',
            'a': 'a',
            'A': 'A',
            'g': 'h',
            'G': 'H',
            'h': 'hh',
            'H': 'HH',
            'i': 'mm',
            's': 'ss',
            'u': 'SSS',
            'e': 'zz', // TODO: full timezone id
            'O': 'ZZ',
            'P': 'Z',
            'T': 'zz',
            'U': 'X'
        };

        $.each(replacements, function (phpToken, momentToken) {
            format = format.replace(phpToken, momentToken);
        });

        return format;
    };

    element.find('input.date-or-time')
        .each(function () {
            $(this).datetimepicker({
                format: convertPhpDateFormatToMomentFormat($(this).attr('data-date-format')),
                minDate: $(this).attr('data-min-date') ? new Date($(this).attr('data-min-date')) : null,
                maxDate: $(this).attr('data-max-date') ? new Date($(this).attr('data-max-date')) : null,
                useCurrent: !$(this).attr('data-dont-use-current')
            });
        });

    element.find('.date-or-time-range')
        .each(function () {
            var startInput = $(this).find('input.date-or-time.start-input');
            var endInput = $(this).find('input.date-or-time.end-input');

            startInput.on("dp.change", function (e) {
                endInput.data("DateTimePicker").minDate(e.date);
            });

            endInput.on("dp.change", function (e) {
                startInput.data("DateTimePicker").maxDate(e.date);
            });
        });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('ul.list-field').each(function () {
        var listOfFields = $(this);
        var templateField = $(this).find('.list-field-template');
        var addButton = $(this).find('.btn-add-field');
        var isInvalidating = false;

        var minFields = listOfFields.attr('data-min-elements');
        var maxFields = listOfFields.attr('data-max-elements');

        var getAmountOfInputs = function () {
            return listOfFields.children('.list-field-item').length;
        };

        var invalidateControl = function () {
            if (isInvalidating) {
                return;
            }

            isInvalidating = true;

            var amountOfInputs = getAmountOfInputs();

            addButton.prop('disabled', getAmountOfInputs() >= maxFields);
            listOfFields.find('.btn-remove-field').prop('disabled', getAmountOfInputs() <= minFields);

            while (amountOfInputs < minFields) {
                addNewField();
                amountOfInputs++;
            }

            isInvalidating = false;
        };

        var addNewField = function () {
            listOfFields.append(
                templateField.clone()
                    .removeClass('list-field-template')
                    .addClass('list-field-item')
            );

            invalidateControl();
        };

        listOfFields.on('click', '.btn-remove-field', function () {
            $(this).closest('.list-field-item').remove();

            invalidateControl();
        });

        invalidateControl();

        if (minFields !== null && minFields === maxFields) {
            addButton.closest('.list-field-add').remove();
            listOfFields.find('.btn-remove-field').remove();
        }
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('select[multiple]').multiselect({
        enableFiltering: true,
        includeSelectAllOption: true
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input[type="number"][data-max-decimal-places]').each(function () {
        $(this).attr('data-parsley-max-decimal-places', $(this).attr('data-max-decimal-places'));
    });

    element.find('input[type="number"][data-greater-than]').each(function () {
        $(this).attr('data-parsley-gt', $(this).attr('data-greater-than'));
    });

    element.find('input[type="number"][data-less-than]').each(function () {
        $(this).attr('data-parsley-lt', $(this).attr('data-less-than'));
    });
});
Dms.form.initializeCallbacks.push(function (element) {

});
Dms.form.initializeCallbacks.push(function (element) {

});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input[type="ip-address"]')
        .attr('type', 'text')
        .attr('data-parsley-ip-address', '1');
});
Dms.form.initializeCallbacks.push(function (element) {

});
//# sourceMappingURL=app.js.map
