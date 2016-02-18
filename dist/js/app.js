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
Dms.global.initializeCallbacks.push(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
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
Dms.form.validation.clearMessages = function (form) {
    form.removeClass('has-error');
    form.find('.form-group').removeClass('has-error');
    form.find('.help-block.help-block-error').remove();
};

Dms.form.validation.displayMessages = function (form, fieldMessages, generalMessages) {
    if (!fieldMessages && !generalMessages) {
        return;
    }

    form.addClass('has-error');

    var makeHelpBlock = function () {
        return $('<span />').addClass(['help-block', 'help-block-error']);
    };

    var helpBlock = makeHelpBlock();

    $.each(generalMessages, function (index, message) {
        helpBlock.append($('<strong />').text(message));
    });

    form.prepend(helpBlock);

    var flattenedFieldMessages = {};

    var visitMessages = function (fieldName, messages) {
        if ($.isArray(messages)) {
            $.each(messages, function (index, message) {
                flattenedFieldMessages[fieldName] = message;
            });
        } else {
            $.each(messages.constraints, function (index, message) {
                flattenedFieldMessages[fieldName] = message;
            });

            $.each(messages.fields, function (fieldElementName, elementMessages) {
                visitMessages(fieldName + '[' + fieldElementName + ']', elementMessages);
            });
        }
    };
    $.each(fieldMessages, visitMessages);

    $.each(flattenedFieldMessages, function (fieldName, messages) {
        var fieldGroup = form.find('.form-group[data-field-name="' + fieldName + '"]');

        var helpBlock = makeHelpBlock();
        $.each(messages, function (index, message) {
            helpBlock.append($('<strong />').text(message));
        });

        fieldGroup.prepend(helpBlock);
    });
};
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
};
Dms.chart.initializeCallbacks.push(function (element) {

    element.find('.dms-chart-control').each(function () {
        var control = $(this);
        var chartContainer = control.find('chart.dms-chart-container');
        var loadChartUrl = control.attr('data-load-chart-url');


        var criteria = {
            orderings: [],
            conditions: []
        };

        var currentAjaxRequest;

        var loadCurrentData = function () {
            chartContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            currentAjaxRequest = $.ajax({
                url: loadChartUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (chartData) {
                chartContainer.html(chartData);
                Dms.chart.initialize(chartContainer);
            });

            currentAjaxRequest.fail(function () {
                chartContainer.addClass('error');

                swal({
                    title: "Could not load chart data",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                chartContainer.removeClass('loading');
            });
        };

        loadCurrentData();
    });
});
Dms.chart.initializeCallbacks.push(function () {
    $('.dms-chart.dms-graph-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var chartType = !!chart.attr('data-chart-type');
        var horizontalAxisKey = chart.attr('data-horizontal-axis-key');
        var verticalAxisKeys = JSON.parse(chart.attr('data-vertical-axis-keys'));
        var verticalAxisLabels = JSON.parse(chart.attr('data-vertical-axis-labels'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.guidGenerator());
        }

        var morrisConfig = {
            element: chart.attr('id'),
            data: chartData,
            xkey: horizontalAxisKey,
            ykeys: verticalAxisKeys,
            labels: verticalAxisLabels
        };

        if (chartType === 'bar') {
            Morris.Bar(morrisConfig);
        } else if (chartType === 'area') {
            Morris.Area(morrisConfig);
        } else {
            Morris.Line(morrisConfig);
        }
    });
});
Dms.chart.initializeCallbacks.push(function () {
    $('.dms-chart.dms-pie-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.guidGenerator());
        }

        Morris.Donut({
            element: chart.attr('id'),
            data: chartData
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input[type=checkbox].single-checkbox').iCheck();
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('.list-of-checkboxes').each(function () {
        var listOfCheckboxes = $(this);
        listOfCheckboxes.find('input[type=checkbox]').iCheck();

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
    element.find('input.dms-colour-input').each(function () {
        var config = {
            showInput: true,
            showPalette: true
        };

        if ($(this).hasClass('dms-colour-input-rgb')) {
            config.preferredFormat = 'rgb';
        } else if ($(this).hasClass('dms-colour-input-rgba')) {
            config.preferredFormat = 'rgba';
        }

        $(this).spectrum(config);
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
    element.find('.dms-inner-form').each(function () {
        var innerForm = $(this);

        if (innerForm.attr('data-readonly')) {
            innerForm.find(':input').attr('readonly', 'readonly');
        }
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
Dms.form.initializeCallbacks.push(function (element) {

    var fieldCounter = 1;

    element.find('.dms-form-fieldset .form-group').each(function () {
        var fieldLabel = $(this).children('label[data-for]');
        var forFieldName = fieldLabel.attr('data-for');

        if (forFieldName) {
            var forField = $(this).first('*[name="' + forFieldName + '"], .dms-inner-form[data-name="' + forFieldName + '"]');

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
        var parsley = form.parsley();
        var stageElements = form.find('.dms-form-stage');
        var submitButtons = form.find('input[type=submit], button[type=submit]');

        var updateFormValidity = function () {
            var isValid = parsley.isValid()
                && form.find('.has-error').length === 0
                && form.find('.dms-form-stage').length === form.find('.dms-form-stage.loaded').length;

            submitButtons.prop('disabled', !isValid);
        };

        form.on('change input', '*[name]:input', updateFormValidity);
        updateFormValidity();

        stageElements.each(function () {
            var currentStage = $(this);

            if (currentStage.is('.loaded')) {
                return;
            }

            var previousStages = currentStage.prevAll('.dms-form-stage');
            var loadStageUrl = currentStage.attr('data-stage-load-url');
            var dependentFields = currentStage.attr('data-stage-dependent-fields');
            var dependentFieldsSelector = null;
            var currentAjaxRequest = null;

            if (dependentFields) {
                var dependentFieldNames = JSON.parse(dependentFields);

                var selectors = [];
                $.each(dependentFieldNames, function (index, fieldName) {
                    selectors.push('*[name="' + fieldName + '"]:input');
                    selectors.push('*[name^="' + fieldName + '["][name$="]"]:input');
                });

                dependentFieldsSelector = selectors.join(',');
            } else {
                dependentFieldsSelector = '*[name]:input';
            }

            previousStages.on('change input', dependentFieldsSelector, function () {
                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                }

                currentStage.removeClass('loaded');
                currentStage.addClass('loading');

                var formData = new FormData();

                previousStages.find(dependentFieldsSelector).each(function () {
                    var fieldName = $(this).attr('name');

                    if ($(this).is('[type=file]')) {
                        $.each(this.files, function (index, file) {
                            formData.append(fieldName, file);
                        });
                    } else {
                        formData.append(fieldName, $(this).val());
                    }
                });

                currentAjaxRequest = $.ajax({
                    url: loadStageUrl,
                    type: 'post',
                    processData: false,
                    contentType: false,
                    dataType: 'html',
                    data: formData
                });

                currentAjaxRequest.done(function (html) {
                    currentStage.removeClass('loading');
                    currentStage.addClass('loaded');
                    Dms.form.validation.clearMessages(form);
                    currentStage.html(html);
                    Dms.form.initialize(currentStage);
                });

                currentAjaxRequest.fail(function (xhr) {
                    switch (xhr.status) {
                        case 422: // Unprocessable Entity (validation failure)
                            var validation = JSON.parse(xhr.responseText);
                            Dms.form.validation.displayMessages(form, validation.fields, validation.constraints);
                            break;

                        case 400: // Bad request
                            swal({
                                title: "Could not load form",
                                text: JSON.parse(xhr.responseText).message,
                                type: "error"
                            });
                            break;

                        default: // Unknown error
                            swal({
                                title: "Could not load form",
                                text: "An unexpected error occurred",
                                type: "error"
                            });
                            break;
                    }
                });

                currentAjaxRequest.always(updateFormValidity);
            });
        });
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
Dms.table.initializeCallbacks.push(function (element) {

    element.find('.dms-table-control').each(function () {
        var control = $(this);
        var tableContainer = control.find('.dms-table-container');
        var table = tableContainer.find('table.dms-table');
        var filterForm = control.find('.dms-table-quick-filter-form');
        var loadRowsUrl = control.attr('data-load-rows-url');
        var reorderRowsUrl = control.attr('data-reorder-row-action-url');

        var currentPage = 0;

        var getItemsPerPage = function () {
            return filterForm.find('select[name=items_per_page]').val()
        };

        var criteria = {
            orderings: [],
            conditions: []
        };

        var currentAjaxRequest;

        var loadCurrentPage = function () {
            tableContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            criteria.offset = currentPage * getItemsPerPage();
            criteria.max_rows = getItemsPerPage();

            currentAjaxRequest = $.ajax({
                url: loadRowsUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (tableData) {
                table.html(tableData);
                Dms.table.initialize(tableContainer);
            });

            currentAjaxRequest.fail(function () {
                tableContainer.addClass('error');

                swal({
                    title: "Could not load table data",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                tableContainer.removeClass('loading');
            });
        };

        filterForm.find('button').click(function () {
            criteria.orderings = [
                {
                    component: filterForm.find('[name=component]').val(),
                    direction: filterForm.find('[name=direction]').val()
                }
            ];

            criteria.conditions = [
                // TODO:
            ];

            loadCurrentPage();
        });

        loadCurrentPage();
    });
});
Dms.widget.initializeCallbacks.push(function () {
    $('.dms-widget-unparameterized-action, .dms-widget-parameterized-action').each(function () {
        var widget = $(this);
        var button = widget.find('button');

        if (button.is('.btn-danger')) {
            var isConfirmed = false;

            button.click(function () {
                if (isConfirmed) {
                    isConfirmed = false;
                    return;
                }

                swal({
                    title: "Are you sure?",
                    text: "This will execute the '" + widget.attr('data-action-label') + "' action",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes proceed!"
                }, function () {
                    isConfirmed = true;
                    $(this).click();
                });

                return false;
            });
        }
    });
});
//# sourceMappingURL=app.js.map
