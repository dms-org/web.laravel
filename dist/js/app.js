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
        responseHandler: null // @see ./services/action.js
    },
    alerts: {
        add: null // @see ./services/alerts.js
    },
    form: {
        initialize: function (element) {
            var callbacks = Dms.form.initializeCallbacks.concat(Dms.form.initializeValidationCallbacks);

            $.each(callbacks, function (index, callback) {
                callback(element);
            });
        },
        validation: {}, // @see ./services/form-validation.js
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
Dms.action.responseHandler = function (response) {
    if (typeof response.redirect !== 'undefined') {
        if (typeof response.message !== 'undefined') {
            Cookies.set('dms-flash-alert', {
                message: response.message,
                type: response.message_type || 'success'
            });
        }

        window.location.href = response.redirect;
        return;
    }

    if (typeof response.message !== 'undefined') {
        Dms.alerts.add('success', response.message);
    }

    if (typeof response.files !== 'undefined') {
        swal({
            title: "Downloading files",
            text: "Please wait while your download begins. <br> Files: " + response.files.join(', '),
            type: "info",
            showConfirmButton: false,
            showLoaderOnConfirm: true
        });

        $.each(response.files, function (index, file) {
            $('<iframe />')
                .attr('src', Dms.config.routes.downloadFile(file.token))
                .css('display', 'none')
                .appendTo($(document.body));
        });

        var downloadsBegun = 0;
        var checkIfDownloadsHaveBegun = function () {

            $.each(response.files, function (index, file) {
                var fileCookieName = 'file-download-' + file.token;

                if (Cookies.get(fileCookieName)) {
                    downloadsBegun++;
                    Cookies.remove(fileCookieName)
                }
            });

            if (downloadsBegun < response.files.length) {
                setTimeout(checkIfDownloadsHaveBegun, 100);
            } else {
                swal.close();
            }
        };

        checkIfDownloadsHaveBegun();
    }
};
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
    var flashMessage = Cookies.getJSON('dms-flash-alert');

    if (flashMessage) {
        Cookies.remove('dms-flash-alert');

        Dms.alerts.add(flashMessage.type, flashMessage.message);
    }
});
Dms.form.initializeCallbacks.push(function () {
    var submitButtons = $('.dms-staged-form, .dms-run-action-form').find('[type=submit].btn-danger');

    submitButtons.click(function (e) {
        var button = $(this);

        var result = button.triggerHandler('before-confirmation');
        if (result === false) {
            e.stopImmediatePropagation();
            return false;
        }

        if (button.data('dms-has-confirmed')) {
            button.data('dms-has-confirmed', false);
            return;
        }

        swal({
            title: "Are you sure?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Yes!"
        }, function () {
            button.data('dms-has-confirmed', true);
            button.click();
        });

        e.stopImmediatePropagation();
        return false;
    });
});
Dms.global.initializeCallbacks.push(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});
$(document).ready(function () {
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    $.widget.bridge('uibutton', $.ui.button);
});
Dms.global.initializeCallbacks.push(function () {
    $('a').click(function (e) {
        if ($(this).attr('disabled')) {
            e.stopImmediatePropagation();

            return false;
        }

        return true;
    });
});
Dms.global.initializeCallbacks.push(function () {
    var navigationFilter = $('.dms-nav-quick-filter');
    var packagesNavigation = $('.dms-packages-nav');
    var navigationSections = packagesNavigation.find('li.treeview');
    var navigationLabels = packagesNavigation.find('.dms-nav-label');

    navigationFilter.on('input', function () {
        var filterBy = $(this).val();

        navigationSections.hide();
        var sectionsToShow = [];
        navigationLabels.each(function (index, navItem) {
            navItem = $(navItem);
            var label = navItem.text();

            var doesContainFilter = label.toLowerCase().indexOf(filterBy.toLowerCase()) !== -1;
            navItem.closest('li').toggle(doesContainFilter);

            if (doesContainFilter) {
                navItem.closest('ul.treeview-menu').toggle(doesContainFilter).addClass('menu-open');
                navItem.parents('li.treeview').show();

                if (navItem.is('.dms-nav-label-group')) {
                    sectionsToShow.push(navItem.closest('li.treeview').get(0));
                }
            }
        });

        $(sectionsToShow).find('li').show();
        $(sectionsToShow).find('ul.treeview-menu').show().addClass('menu-open');
    });

    navigationFilter.on('keyup', function (event) {
        var enterKey = 13;

        if (event.keyCode === enterKey) {
            var link = packagesNavigation.find('a[href!="javascript:void(0)"]:visible').first().attr('href');
            window.location.href = link;
        }
    });
});
Dms.utilities.countDecimals = function (value) {
    if (value % 1 != 0) {
        return value.toString().split(".")[1].length;
    }

    return 0;
};

Dms.utilities.idGenerator = function() {
    var S4 = function() {
        return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return 'id' + (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
};
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

window.ParsleyValidator
    .addValidator('min-elements', {
        requirementType: 'number',
        validateMultiple: function (value, requirement) {
            return value.length >= requirement;
        },
        messages: {
            en: 'At least %s options must be selected'
        }
    });

window.ParsleyValidator
    .addValidator('max-elements', {
        requirementType: 'number',
        validateMultiple: function (value, requirement) {
            return value.length <= requirement;
        },
        messages: {
            en: 'No more than %s options can be selected'
        }
    });


Dms.form.validation.clearMessages = function (form) {
    form.find('.form-group').removeClass('has-error');
    form.find('.help-block.help-block-error').remove();
};

Dms.form.validation.displayMessages = function (form, fieldMessages, generalMessages) {
    if (!fieldMessages && !generalMessages) {
        return;
    }

    var makeHelpBlock = function () {
        return $('<span />').addClass('help-block help-block-error');
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
                if (typeof flattenedFieldMessages[fieldName] === 'undefined') {
                    flattenedFieldMessages[fieldName] = [];
                }

                flattenedFieldMessages[fieldName].push(message);
            });
        } else {
            $.each(messages.constraints, function (index, message) {
                if (typeof flattenedFieldMessages[fieldName] === 'undefined') {
                    flattenedFieldMessages[fieldName] = [];
                }

                flattenedFieldMessages[fieldName].push(message);
            });

            $.each(messages.fields, function (fieldElementName, elementMessages) {
                visitMessages(fieldName + '[' + fieldElementName + ']', elementMessages);
            });
        }
    };
    $.each(fieldMessages, visitMessages);

    $.each(flattenedFieldMessages, function (fieldName, messages) {
        var fieldMessages = form.find('.form-group[data-field-name="' + fieldName + '"]');
        var validationMessagesContainer = fieldMessages.find('.dms-validation-messages-container');

        var helpBlock = makeHelpBlock();
        $.each(messages, function (index, message) {
            helpBlock.append($('<strong />').text(message));
        });

        fieldMessages.addClass('has-error');
        validationMessagesContainer.prepend(helpBlock);
    });
};
window.ParsleyConfig = {
    successClass: "has-success",
    errorClass: "has-error",
    classHandler: function (el) {
        return el.$element.closest(".form-group");
    },
    errorsWrapper: "<span class='help-block'></span>",
    errorTemplate: "<span></span>"
};
Dms.global.initializeCallbacks.push(function () {
    window.ParsleyValidator.addCatalog('en', {
        defaultMessage: "This value seems to be invalid.",
        type: {
            email: "This value should be a valid email.",
            url: "This value should be a valid url.",
            number: "This value should be a valid number.",
            integer: "This value should be a valid integer.",
            digits: "This value should be digits.",
            alphanum: "This value should be alphanumeric."
        },
        notblank: "This value should not be blank.",
        required: "This value is required.",
        pattern: "This value seems to be invalid.",
        min: "This value should be greater than or equal to %s.",
        max: "This value should be lower than or equal to %s.",
        range: "This value should be between %s and %s.",
        minlength: "This value is too short. It should have %s characters or more.",
        maxlength: "This value is too long. It should have %s characters or fewer.",
        length: "This character length is invalid. It should be between %s and %s characters long.",
        mincheck: "You must select at least %s choices.",
        maxcheck: "You must select %s choices or fewer.",
        check: "You must select between %s and %s choices.",
        equalto: "This must match the confirmation field."
    }, true);
});
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
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

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
            chart.attr('id', Dms.utilities.idGenerator());
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
            chart.attr('id', Dms.utilities.idGenerator());
        }

        Morris.Donut({
            element: chart.attr('id'),
            data: chartData
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input[type=checkbox].single-checkbox').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        increaseArea: '20%'
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('.list-of-checkboxes').each(function () {
        var listOfCheckboxes = $(this);
        listOfCheckboxes.find('input[type=checkbox]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            increaseArea: '20%'
        });

        var firstCheckbox = listOfCheckboxes.find('input[type=checkbox]').first();
        firstCheckbox.attr('data-parsley-min-elements', listOfCheckboxes.attr('data-min-elements'));
        firstCheckbox.attr('data-parsley-max-elements', listOfCheckboxes.attr('data-max-elements'));
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
Dms.table.initializeCallbacks.push(function (element) {

    element.find('.dms-table-control').each(function () {
        var control = $(this);
        var tableContainer = control.find('.dms-table-container');
        var table = tableContainer.find('table.dms-table');
        var filterForm = control.find('.dms-table-quick-filter-form');
        var rowsPerPageSelect = control.find('.dms-table-rows-per-page-form select');
        var paginationPreviousButton = control.find('.dms-table-pagination .dms-pagination-previous');
        var paginationNextButton = control.find('.dms-table-pagination .dms-pagination-next');
        var loadRowsUrl = control.attr('data-load-rows-url');
        var reorderRowsUrl = control.attr('data-reorder-row-action-url');
        var stringFilterableComponentIds = JSON.parse(control.attr('data-string-filterable-component-ids')) || [];

        var currentPage = 0;

        var criteria = {
            orderings: [],
            condition_mode: 'or',
            conditions: [],
            offset: 0,
            max_rows: rowsPerPageSelect.val()
        };

        var currentAjaxRequest;

        var loadCurrentPage = function () {
            tableContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            criteria.offset = currentPage * criteria.max_rows;

            currentAjaxRequest = $.ajax({
                url: loadRowsUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (tableData) {
                table.html(tableData);
                Dms.table.initialize(tableContainer);
                Dms.form.initialize(tableContainer);

                if (table.find('tbody tr').length < criteria.max_rows) {
                    paginationNextButton.attr('disabled', true);
                }
            });

            currentAjaxRequest.fail(function () {
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

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

            criteria.conditions = [];

            var filterByString = filterForm.find('[name=filter]').val();

            if (filterByString) {
                $.each(stringFilterableComponentIds, function (index, componentId) {
                    criteria.conditions.push({
                        component: componentId,
                        operator: 'string-contains-case-insensitive',
                        value: filterByString
                    });
                });
            }

            loadCurrentPage();
        });

        filterForm.find('input[name=filter]').on('keyup', function (event) {
            var enterKey = 13;

            if (event.keyCode === enterKey) {
                filterForm.find('button').click();
            }
        });

        rowsPerPageSelect.on('change', function () {
            criteria.max_rows = $(this).val();

            loadCurrentPage();
        });

        paginationPreviousButton.click(function () {
            paginationNextButton.attr('disabled', false);
            currentPage--;
            loadCurrentPage();
        });

        paginationNextButton.click(function () {
            paginationPreviousButton.attr('disabled', false);
            currentPage++;
            loadCurrentPage();
        });

        paginationPreviousButton.click(function () {
            currentPage--;
            loadCurrentPage();
        });

        paginationPreviousButton.attr('disabled', true);

        loadCurrentPage();
    });
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
        var parsley = form.parsley(window.ParsleyConfig);
        var stageElements = form.find('.dms-form-stage');
        var submitButtons = form.find('input[type=submit], button[type=submit]');

        var isFormValid = function () {
            return parsley.isValid()
                && form.find('.has-error').length === 0
                && form.find('.dms-form-stage').length === form.find('.dms-form-stage.loaded').length;
        };

        submitButtons.on('click before-confirmation', function (e) {
            parsley.validate();

            if (!isFormValid()) {
                e.stopImmediatePropagation();
                return false;
            }
        });

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
                    if (currentAjaxRequest.statusText === 'abort') {
                        return;
                    }

                    switch (xhr.status) {
                        case 422: // Unprocessable Entity (validation failure)
                            var validation = JSON.parse(xhr.responseText);
                            Dms.form.validation.displayMessages(form, validation.messages.fields, validation.messages.constraints);
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
            });
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('form.dms-staged-form, form.dms-run-action-form').each(function () {
        var form = $(this);
        var afterRunCallbacks = [];
        var submitButtons = form.find('input[type=submit], button[type=submit]');
        var submitMethod = form.attr('method');
        var submitUrl = form.attr('action');

        form.on('submit', function (e) {
            e.preventDefault();

            Dms.form.validation.clearMessages(form);

            var formData = new FormData(form.get(0));

            submitButtons.prop('disabled', true);
            submitButtons.addClass('ladda-button').attr('data-style', 'expand-right');
            submitButtons.addClass('ladda-button');
            var ladda = Ladda.create(submitButtons.get(0));
            ladda.start();

            var currentAjaxRequest = $.ajax({
                url: submitUrl,
                type: submitMethod,
                processData: false,
                contentType: false,
                dataType: 'json',
                data: formData,
                xhr: function() {
                    var xhr = $.ajaxSettings.xhr();

                    if(xhr.upload){
                        xhr.upload.addEventListener('progress', function (event) {
                            if (event.lengthComputable) {
                                var percentComplete = event.loaded / event.total;
                                ladda.setProgress(percentComplete);
                            }
                        }, false);
                    }

                    return xhr;
                }
            });

            currentAjaxRequest.done(function (data) {
                Dms.action.responseHandler(data);
                $.each(afterRunCallbacks, function (index, callback) {
                    callback(data);
                });
            });

            currentAjaxRequest.fail(function (xhr) {
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

                switch (xhr.status) {
                    case 422: // Unprocessable Entity (validation failure)
                        var validation = JSON.parse(xhr.responseText);
                        Dms.form.validation.displayMessages(form, validation.messages.fields, validation.messages.constraints);
                        break;

                    default: // Unknown error
                        swal({
                            title: "Could not submit form",
                            text: "An unexpected error occurred",
                            type: "error"
                        });
                        break;
                }
            });

            currentAjaxRequest.always(function () {
                submitButtons.prop('disabled', false);
                ladda.stop();
            });
        });

        var parentToRemove = form.attr('data-after-run-remove-closest');
        if (parentToRemove) {
            afterRunCallbacks.push(function () {
                form.closest(parentToRemove).fadeOut(100);
            });
        }

        afterRunCallbacks.push(function () {
            form.find('input[type=password]').val('');
        });
    });
});
Dms.form.initializeValidationCallbacks.push(function (element) {

    element.find('.dms-form-fields').each(function () {
        if (!$(this).attr('id')) {
            $(this).attr('id', Dms.utilities.idGenerator());
        }
    });

    element.find('.dms-form-fields').each(function () {
        var formFieldSection = $(this);
        var formFieldsGroupId = formFieldSection.attr('id');


        var buildElementSelector = function (fieldName) {
            return '#' + formFieldsGroupId + ' *[name="' + fieldName + '"]';
        };

        var fieldValidations = {
            'data-equal-fields': 'data-parsley-equalto',
            'data-greater-than-fields': 'data-parsley-gt',
            'data-greater-than-or-equal-fields': 'data-parsley-gte',
            'data-less-than-fields': 'data-parsley-lt',
            'data-less-than-or-equal-fields': 'data-parsley-lte'
        };

        $.each(fieldValidations, function (validationAttr, parsleyAttr) {
            var fieldsMap = formFieldSection.attr(validationAttr);

            if (fieldsMap) {
                $.each(JSON.parse(fieldsMap), function (fieldName, otherFieldName) {
                    var field = $(buildElementSelector(fieldName));
                    field.attr(parsleyAttr, buildElementSelector(otherFieldName));
                });
            }
        });
    });

    element.find('form.dms-staged-form').each(function () {
        var form = $(this);
        form.parsley(window.ParsleyConfig);

        form.find('.dms-form-fields').each(function (index) {
            $(this).find(':input').attr('data-parsley-group', 'validation-group-' + index);
        });
    });

    element.find('form.dms-form').each(function () {
        $(this).parsley(window.ParsleyConfig);
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
