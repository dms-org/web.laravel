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
Dms.action.responseHandler = function (httpStatusCode, actionUrl, response) {
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
        Dms.alerts.add(response.message_type || 'success', response.message);
    }

    if (typeof response.files !== 'undefined') {
        var fileNames = [];


        $.each(response.files, function (index, file) {
            fileNames.push(file.name);
        });


        swal({
            html: true,
            title: "Downloading files",
            text: "Please wait while your download begins.\r\n Files: " + fileNames.join(', '),
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
Dms.ajax.formData = function (form) {
    var formValues = {};
    var nativeFormData = new FormData(form);

    this.getNativeFormData = function () {
        return nativeFormData;
    };

    this.append = function (name, value, filename) {
        if (typeof formValues[name] === 'undefined') {
            formValues[name] = [];
        }

        formValues[name].push({
            value: value,
            filename: filename
        });

        if (typeof filename !== 'undefined') {
            nativeFormData.append(name, value, filename);
        } else {
            nativeFormData.append(name, value);
        }

        return nativeFormData;
    };

    this.getFormValues = function () {
        return formValues;
    };

    this.toQueryString = function () {
        var params = [];

        $.each(formValues, function (name, entries) {
            $.each(entries, function (index, entry) {
                params.push({name: name, value: entry.value});
            });
        });

        return $.param(params);
    };
};

Dms.ajax.createFormData = function (form) {
    return new Dms.ajax.formData(form);
};

Dms.ajax.convertResponse = function (dataType, response) {
    if (dataType === 'json') {
        return JSON.parse(response);
    } else if (dataType === 'xml') {
        return $.parseXML(dataType);
    }

    return response;
};

Dms.ajax.parseData = function (data) {
    if (typeof data === 'undefined' || data === null) {
        return [];
    }

    if (data instanceof Dms.ajax.formData) {
        return data.getFormValues();
    }

    var dataMap = {};

    var queryString = $.param(data);
    $.each(queryString.split('&'), function (index, parameter) {
        var parts = parameter.split('=');
        var name = decodeURIComponent(parts[0]);
        if (typeof dataMap[name] === 'undefined') {
            dataMap[name] = [];
        }

        dataMap[name].push({value: decodeURIComponent(parts[1])});
    });

    return dataMap;
};

Dms.ajax.createRequest = function (options) {
    var filteredInterceptors = [];

    $.each(Dms.ajax.interceptors, function (index, interceptor) {
        if (typeof interceptor.accepts !== 'function' || interceptor.accepts(options)) {
            filteredInterceptors.push(interceptor);
        }
    });

    $.each(filteredInterceptors, function (index, interceptor) {
        if (typeof interceptor.before === 'function') {
            interceptor.before(options);
        }
    });

    var callAfterInterceptors = function (response, data) {
        $.each(filteredInterceptors.reverse(), function (index, interceptor) {
            if (typeof interceptor.after === 'function') {
                var returnValue = interceptor.after(options, response, data);

                if (typeof returnValue !== 'undefined') {
                    data = returnValue;
                }
            }
        });

        return data;
    };

    var responseData;

    var originalErrorCallback = options.error;
    options.error = function (jqXHR, textStatus, errorThrown) {
        callAfterInterceptors(jqXHR);

        if (originalErrorCallback) {
            return originalErrorCallback.apply(this, arguments);
        }
    };

    var originalSuccessCallback = options.success;
    options.success = function (data, textStatus, jqXHR) {
        responseData = data = callAfterInterceptors(jqXHR, data);

        if (originalSuccessCallback) {
            return originalSuccessCallback.apply(this, [data, textStatus, jqXHR]);
        }
    };

    if (options.data instanceof Dms.ajax.formData) {
        options.data = options.data.getNativeFormData();
    }

    var request = $.ajax(options);

    var originalDone = request.done;
    request.done = function (callback) {
        originalDone(function (data, textStatus, jqXHR) {
            callback(responseData, textStatus, jqXHR);
        });
    };

    return request;
};


Dms.alerts.add = function (type, title, message, timeout) {
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

    setTimeout(function () {
        if (alert.is(':visible')) {
            alert.fadeOut();
        }
    }, timeout || 10000);
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
Dms.utilities.getCsrfHeaders = function () {
    return {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    };
};

Dms.global.initializeCallbacks.push(function () {
    $.ajaxSetup({
        headers: Dms.utilities.getCsrfHeaders()
    });
});
$(document).ready(function () {
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    $.widget.bridge('uibutton', $.ui.button);
});
$.extend({
    replaceTag: function (currentElem, newTagObj, keepProps) {
        var $currentElem = $(currentElem);
        var i, $newTag = $(newTagObj).clone();
        if (keepProps) {//{{{
            newTag = $newTag[0];
            newTag.className = currentElem.className;
            $.extend(newTag.classList, currentElem.classList);
            $.extend(newTag.attributes, currentElem.attributes);
        }//}}}
        $currentElem.wrapAll($newTag);
        $currentElem.contents().unwrap();
        // return node; (Error spotted by Frank van Luijn)
        return this; // Suggested by ColeLawrence
    }
});

$.fn.extend({
    replaceTag: function (newTagObj, keepProps) {
        // "return" suggested by ColeLawrence
        return this.each(function() {
            jQuery.replaceTag(this, newTagObj, keepProps);
        });
    }
});
Dropzone.autoDiscover = false;
var getAbsoluteName = function (allElements, element) {
    var name = element.name;

    if (name.substr(-2) === '[]') {
        var inputsWithSameNameBefore = allElements
            .filter(function (index, otherElement) {
                return otherElement.name === name;
            })
            .filter(function (index, otherElement) {
                var preceding = 4;
                return otherElement.compareDocumentPosition(element) & preceding;
            });

        name = name.substr(0, name.length - 2) + '[' + inputsWithSameNameBefore.length + ']';
    }

    return name;
};

Dms.form.initializeCallbacks.push(function (element) {
    element.find('form').each(function () {
        var form = $(this);

        var allInputs = form.find(':input');
        form.on('dms-form-updated', function () {
            allInputs = form.find(':input');
        });

        var changedInputs = {};
        form.data('dms-changed-inputs', changedInputs);

        form.on('change input', '*[name]:input', function () {
            changedInputs[getAbsoluteName(allInputs, this)] = true;
        })
    });
});

Dms.global.initializeCallbacks.push(function () {

    $.fn.getValues = function (onlyChanged) {
        var $els = this.find(':input');
        var els = $els.get();
        var changedInputs = $(this).closest('form, .dms-staged-form').data('dms-changed-inputs') || {};

        var data = {};

        $.each(els, function () {
            if (this.name && !this.disabled && (this.checked
                || /select|textarea/i.test(this.nodeName)
                || /text|hidden|password/i.test(this.type))) {
                var absoluteName = getAbsoluteName($els, this);

                if (onlyChanged && !changedInputs[absoluteName]) {
                    return;
                }

                data[absoluteName] = $(this).val();
            }
        });

        return data;
    };

    $.fn.restoreValues = function (data) {
        var $els = this.find(':input');
        var els = $els.get();

        $.each(els, function () {
            if (!this.name) {
                return;
            }

            var name = getAbsoluteName($els, this);

            if (data[name]) {
                var value = data[name];
                var $this = $(this);

                if (this.type == 'checkbox' || this.type == 'radio') {
                    $this.attr("checked", value === $.val());
                } else {
                    $this.val(value);
                }
            }
        });

        return this;
    };
});
Dms.form.stages.makeDependentFieldSelectorFor = function (dependentFieldNames, selector, dontAddKnownData) {
    var selectors = [];

    if (dependentFieldNames) {
        $.each(dependentFieldNames, function (index, fieldName) {
            selectors.push(selector + '[name="' + fieldName + '"]:input');
            selectors.push(selector + '[name^="' + fieldName + '["][name$="]"]:input');
        });

        return selectors.join(',');
    } else {
        selectors.push(selector + '[name]:input');
    }

    if (!dontAddKnownData) {
        selectors.push('.dms-form-stage-known-data ' + selector + ':input');
    }

    return selectors.join(',');
};

Dms.form.stages.makeDependentFieldSelectorForStageMap = function (stageToDependentFieldMap, selector) {
    var selectors = [];

    $.each(stageToDependentFieldMap, function (stageNumber, dependentFields) {
        if (dependentFields === '*') {
            selectors.push('.dms-form-stage[data-stage-number="' + stageNumber + '"] :input');
        } else {
            var fieldsInStageSelector = Dms.form.stages.makeDependentFieldSelectorFor(
                dependentFields,
                '.dms-form-stage[data-stage-number="' + stageNumber + '"] *',
                true
            );

            selectors = selectors.concat(fieldsInStageSelector);
        }
    });

    selectors.push('.dms-form-stage-known-data ' + selector + ':input');
    return selectors.join(',');
};

Dms.form.stages.createFormDataFromFields = function (fields) {
    var formData = Dms.ajax.createFormData();

    fields.filter('[name]').each(function () {
        var field = $(this);
        var fieldName = field.attr('name');

        if (field.is('[type=file]')) {
            $.each(this.files, function (index, file) {
                formData.append(fieldName, file);
            });
        } else if (field.is('[type=checkbox], [type=radio]')) {
            if (field.is(':checked')) {
                formData.append(fieldName, field.val());
            }
        } else {
            formData.append(fieldName, field.val());
        }
    });

    return formData;
};
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

Dms.utilities.idGenerator = function () {
    var S4 = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return 'id' + (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
};

Dms.utilities.combineFieldNames = function (outer, inner) {
    if (inner.indexOf('[') === -1) {
        return outer + '[' + inner + ']';
    }

    var firstInner = inner.substring(0, inner.indexOf('['));
    var afterFirstInner = inner.substring(inner.indexOf('['));

    return outer + '[' + firstInner + ']' + afterFirstInner;
};

Dms.utilities.areUrlsEqual = function (first, second) {
    return first.replace(/\/+$/, '') === second.replace(/\/+$/, '');
};

Dms.utilities.downloadFileFromUrl = function (url) {
    $('<iframe />')
        .attr({'src': url})
        .hide()
        .appendTo('body');
};

Dms.utilities.isTouchDevice = function () {
    try {
        document.createEvent("TouchEvent");
        return true;
    } catch (e) {
        return false;
    }
};

Dms.utilities.convertPhpDateFormatToMomentFormat = function (format) {
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

    var newFormat = '';

    $.each(format.split(''), function (index, char) {
        if (replacements[char]) {
            newFormat += replacements[char];
        } else {
            newFormat += char;
        }
    });

    return newFormat;
};
window.Parsley.addValidator('ipAddress', {
    requirementType: 'boolean',
    validateString: function (value) {
        var ipV4Regex = /^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.|$)){4}$/;
        var ipV6Regex = /^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])|([0-9a-fA-F]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[0-9]){0,1}[0-9]))$/;

        if (ipV4Regex.test(value)) {
            return true;
        }

        if (ipV6Regex.test(value)) {
            return true;
        }

        return false;
    },
    messages: {
        en: 'This value should be a valid ip address'
    }
});

window.Parsley.addValidator('maxDecimalPoints', {
    requirementType: 'integer',
    validateString: function (value, requirement) {
        return Dms.utilities.countDecimals(value) <= requirement;
    },
    messages: {
        en: 'This value should have a maximum of %d decimal places'
    }
});

window.Parsley.addValidator('minElements', {
    requirementType: 'integer',
    validateMultiple: function (value, requirement) {
        return value.length >= requirement;
    },
    messages: {
        en: 'At least %s options must be selected'
    }
});

window.Parsley.addValidator('maxElements', {
    requirementType: 'integer',
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
        var fieldGroup = form.find('.form-group[data-field-name="' + fieldName + '"]').add(
            form.find('.form-group *[data-field-validation-for]')
                .filter(function () {
                    return $(this).attr('data-field-validation-for').indexOf(fieldName) !== -1;
                })
                .closest('.form-group')
        );
        var validationMessagesContainer = fieldGroup.find('.dms-validation-messages-container');

        var helpBlock = makeHelpBlock();
        $.each($.unique(messages), function (index, message) {
            helpBlock.append($('<p />').append($('<strong />').text(message)));
        });

        fieldGroup.addClass('has-error');
        validationMessagesContainer.prepend(helpBlock);
    });
};
window.ParsleyConfig = {
    successClass: "has-success",
    errorClass: "has-error",
    excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    classHandler: function (el) {
        return el.$element.closest(".form-group");
    },
    errorsWrapper: "<span class='help-block dms-validation-message'></span>",
    errorTemplate: "<span></span>"
};
Dms.global.initializeCallbacks.push(function () {
    window.Parsley.addCatalog('en', {
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
        var chartContainer = control.find('.dms-chart-container');
        var chartElement = chartContainer.find('.dms-chart');
        var chartRangePicker = chartContainer.find('.dms-chart-range-picker');
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

            currentAjaxRequest = Dms.ajax.createRequest({
                url: loadChartUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (chartData) {
                chartElement.html(chartData);
                Dms.chart.initialize(chartElement);
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

        chartRangePicker.on('dms-range-updated', function () {
            var horizontalAxis = chartContainer.attr('data-date-axis-name');
            criteria.conditions = [
                {axis: horizontalAxis, operator: '>=', value: chartRangePicker.find('.start-input').val()},
                {axis: horizontalAxis, operator: '<=', value: chartRangePicker.find('.end-input').val()}
            ];

            loadCurrentData();
        });
    });
});
Dms.chart.initializeCallbacks.push(function (element) {
    element.find('.dms-graph-chart').each(function () {
        var chart = $(this);
        var dateFormat = Dms.utilities.convertPhpDateFormatToMomentFormat(chart.attr('data-date-format'));
        var chartData = JSON.parse(chart.attr('data-chart-data'));
        var chartType = chart.attr('data-chart-type');
        var horizontalAxisKey = chart.attr('data-horizontal-axis-key');
        var verticalAxisKeys = JSON.parse(chart.attr('data-vertical-axis-keys'));
        var verticalAxisLabels = JSON.parse(chart.attr('data-vertical-axis-labels'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.idGenerator());
        }

        $.each(chartData, function (index, row) {
            row[horizontalAxisKey] = moment(row[horizontalAxisKey], dateFormat).valueOf();
        });

        var morrisConfig = {
            element: chart.attr('id'),
            data: chartData,
            xkey: horizontalAxisKey,
            ykeys: verticalAxisKeys,
            labels: verticalAxisLabels,
            resize: true,
            redraw: true,
            dateFormat: function (timestamp) {
                return moment(timestamp).format(dateFormat);
            }
        };

        var morrisChart;
        if (chartType === 'bar') {
            morrisChart = Morris.Bar(morrisConfig);
        } else if (chartType === 'area') {
            morrisChart = Morris.Area(morrisConfig);
        } else {
            morrisChart = Morris.Line(morrisConfig);
        }

        $(window).on('resize', function () {
            if (morrisChart.raphael) {
                morrisChart.redraw();
            }
        });
    });
});
Dms.chart.initializeCallbacks.push(function (element) {
    element.find('.dms-pie-chart').each(function () {
        var chart = $(this);
        var chartData = JSON.parse(chart.attr('data-chart-data'));

        if (!chart.attr('id')) {
            chart.attr('id', Dms.utilities.idGenerator());
        }

        var morrisChart = Morris.Donut({
            element: chart.attr('id'),
            data: chartData,
            resize: true,
            redraw: true
        });

        $(window).on('resize', function () {
            if (morrisChart.raphael) {
                morrisChart.redraw();
            }
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
            theme: 'bootstrap'
        };

        if ($(this).hasClass('dms-colour-input-rgb')) {
            config.format = 'rgb';
        } else if ($(this).hasClass('dms-colour-input-rgba')) {
            config.format = 'rgb';
            config.opacity = true;
        }

        $(this).addClass('minicolors').minicolors(config);
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('.dropzone-container').each(function () {
        var container = $(this);
        var uniqueId = Dms.utilities.idGenerator();
        var form = container.closest('.dms-staged-form');
        var dropzoneElement = container.find('.dms-dropzone');
        var fieldName = container.attr('data-name');
        var required = container.attr('data-required');
        var tempFilePrefix = container.attr('data-temp-file-key-prefix');
        var uploadTempFileUrl = container.attr('data-upload-temp-file-url');
        var maxFileSize = container.attr('data-max-size');
        var maxFiles = container.attr('max-files');
        var existingFiles = JSON.parse(container.attr('data-files') || '[]');
        var isMultiple = container.attr('data-multi-upload');

        var maxImageWidth = container.attr('data-max-width');
        var minImageWidth = container.attr('data-min-width');
        var maxImageHeight = container.attr('data-max-height');
        var minImageHeight = container.attr('data-min-height');
        var imageEditor = container.find('.dms-image-editor');

        var getDownloadUrlForFile = function (file) {
            if (file.downloadUrl) {
                return file.downloadUrl;
            }

            if (file.tempFileToken) {
                return container.attr('data-download-temp-file-url').replace('__token__', file.tempFileToken);
            }

            return null;
        };

        var editedImagesQueue = [];
        var isEditingImage = false;

        var showImageEditor = function (file, saveCallback, alwaysCallback, options) {

            if (isEditingImage) {
                editedImagesQueue.push(arguments);
                return;
            }

            isEditingImage = true;
            if (!options) {
                options = {};
            }

            imageEditor.find('.modal-title').text(options.title || 'Edit Image');

            var canvasContainer = imageEditor.find('.dms-canvas-container');

            var imageSrc = getDownloadUrlForFile(file);

            var loadDarkroom = function (imageSrc) {
                var imageElement = $('<img />').attr('src', imageSrc);
                canvasContainer.append(imageElement);

                var darkroom = new Darkroom(imageElement.get(0), $.extend({}, {
                    plugins: {
                        save: false // disable plugin
                    },

                    initialize: function () {
                        imageEditor.modal('show');
                    }
                }, options));

                imageEditor.find('.btn-save-changes').on('click', function () {
                    var blob = window.dataURLtoBlob(darkroom.canvas.toDataURL());

                    imageEditor.modal('hide');

                    blob.name = file.name;
                    saveCallback(blob);
                    alwaysCallback();
                });

                imageEditor.on('hide.bs.modal', function () {
                    canvasContainer.empty();
                    alwaysCallback();

                    imageEditor.unbind('hide.bs.modal');
                    imageEditor.find('.btn-save-changes').unbind('click');

                    isEditingImage = false;

                    if (editedImagesQueue.length > 0) {
                        showImageEditor.apply(null, editedImagesQueue.pop());
                    }
                });
            };

            if (imageSrc) {
                loadDarkroom(imageSrc);
            } else {
                var reader = new FileReader();

                reader.addEventListener("load", function () {
                    loadDarkroom(reader.result);
                }, false);

                reader.readAsDataURL(file);
            }
        };

        var acceptedFiles = JSON.parse(container.attr('data-allowed-extensions') || '[]').map(function (extension) {
            return '.' + extension;
        });

        if (container.attr('data-images-only')) {
            acceptedFiles.push('image/*')
        }

        dropzoneElement.attr('id', 'dropzone-' + uniqueId);
        var dropzone = new Dropzone('#dropzone-' + uniqueId, {
            url: uploadTempFileUrl,
            paramName: 'file',
            maxFilesize: maxFileSize,
            maxFiles: isMultiple ? maxFiles : 1,
            headers: Dms.utilities.getCsrfHeaders(),
            acceptedFiles: acceptedFiles.join(','),

            init: function () {
                var dropzone = this;

                this.on("addedfile", function (file) {
                    var removeButton = Dropzone.createElement(
                        '<button type="button" class="btn btn-sm btn-danger btn-remove-file"><i class="fa fa-times"></i></button>'
                    );

                    removeButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        dropzone.removeFile(file);

                        if (file.action === 'keep-existing') {
                            file.action = 'delete-existing';
                            updateSubmissionState();
                        }

                        if (dropzone.options.maxFiles === 0) {
                            dropzone.options.maxFiles++;
                        }
                    });

                    file.previewElement.appendChild(removeButton);
                });

                this.on("removedfile", function (file) {
                    if (file.action === 'keep-existing') {
                        file.action = 'delete-existing';
                    }

                    updateSubmissionState();
                });

                this.on("complete", function (file) {
                    var downloadButton = Dropzone.createElement(
                        '<button type="button" class="btn btn-sm btn-success btn-download-file"><i class="fa fa-download"></i></button>'
                    );

                    downloadButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        Dms.utilities.downloadFileFromUrl(getDownloadUrlForFile(file));
                    });

                    file.previewElement.appendChild(downloadButton);

                    if (file.width && file.height) {
                        var editImageButton = Dropzone.createElement(
                            '<button type="button" class="btn btn-sm btn-info btn-edit-file"><i class="fa fa-pencil-square-o"></i></button>'
                        );

                        editImageButton.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $(editImageButton).prop('disabled', true);

                            showImageEditor(file, function (newFile) {
                                dropzone.removeFile(file);

                                if (dropzone.options.maxFiles === 0) {
                                    dropzone.options.maxFiles++;
                                }

                                dropzone.addFile(newFile);
                            }, function () {
                                $(editImageButton).prop('disabled', false);
                            });
                        });

                        file.previewElement.appendChild(editImageButton);
                    }
                });

                this.on('success', function (file, response) {
                    file.action = 'store-new';
                    file.tempFileToken = response.tokens['file'];
                    updateSubmissionState();
                });

                this.on("thumbnail", function (file) {
                    if (!file.acceptDimensions && !file.rejectDimensions) {
                        return;
                    }

                    if ((maxImageWidth && file.width > maxImageWidth) || (maxImageHeight && file.height > maxImageHeight)
                        || (minImageWidth && file.width < minImageWidth) || (minImageHeight && file.height < minImageHeight)) {
                        file.rejectDimensions();
                    }
                    else {
                        file.acceptDimensions();
                    }
                });

                $.each(existingFiles, function (index, existingFile) {
                    existingFile.originalIndex = index;
                    existingFile.action = 'keep-existing';
                    existingFile.tempFileToken = null;

                    dropzone.emit("addedfile", existingFile);
                    dropzone.createThumbnailFromUrl(existingFile, existingFile.previewUrl);
                    dropzone.emit("complete", existingFile);

                    if (dropzone.options.maxFiles > 0) {
                        dropzone.options.maxFiles--;
                    }
                });

            },

            accept: function (file, done) {
                if (file.type.indexOf('image') === -1) {
                    done();
                }

                file.acceptDimensions = done;
                file.rejectDimensions = function () {
                    showImageEditor(file, function (editedFile) {
                        dropzone.addFile(editedFile);
                    }, function () {
                        try {
                            dropzone.removeFile(file);
                        } catch (e) {
                        }
                    }, {
                        title: 'The supplied image does not match the required dimensions so it has been resized to: (' + formatRequiredDimensions(file) + ')',
                        minWidth: minImageWidth,
                        minHeight: minImageHeight,
                        maxWidth: maxImageWidth,
                        maxHeight: maxImageHeight
                    })
                };
            }
        });

        var formatRequiredDimensions = function (file) {
            var min = '', max = '';

            if (minImageWidth && minImageHeight) {
                min = 'min: ' + minImageWidth + 'x' + minImageHeight + 'px';
            }
            else if (minImageWidth) {
                min = 'min width: ' + minImageWidth + 'px';
            }
            else if (minImageHeight) {
                min = 'min height: ' + minImageHeight + 'px';
            }

            if (maxImageWidth && maxImageHeight) {
                max = 'max: ' + maxImageWidth + 'x' + maxImageHeight + 'px';
            }
            else if (maxImageWidth) {
                max = 'max width: ' + maxImageWidth + 'px';
            }
            else if (minImageHeight) {
                max = 'max height: ' + minImageHeight + 'px';
            }

            return (min + ' ' + max).trim();
        };

        dropzoneElement.addClass('dropzone');

        var updateSubmissionState = function () {
            form.find('.file-action-' + uniqueId).remove();
            form.find('.file-token-' + uniqueId).remove();

            var allFiles = [];

            $.each(existingFiles.concat(dropzone.getAcceptedFiles()), function (index, file) {
                if (file.action === 'delete-existing') {
                    return;
                }

                if (typeof file.originalIndex !== 'undefined') {
                    allFiles[file.originalIndex] = file;
                    return;
                }

                while (typeof allFiles[index] !== 'undefined') {
                    index++;
                }

                allFiles[index] = file;
            });

            $.each(allFiles, function (index, file) {
                if (!file) {
                    return;
                }

                var fileFieldName;
                fileFieldName = isMultiple
                    ? fieldName + '[' + index + ']'
                    : fieldName;

                form.append($('<input />').attr({
                    'class': 'file-action-' + uniqueId,
                    'type': 'hidden',
                    'name': fileFieldName + '[action]',
                    'value': file.action
                }));

                if (file.tempFileToken) {
                    form.append($('<input />').attr({
                        'class': 'file-token-' + uniqueId,
                        'type': 'hidden',
                        'name': Dms.utilities.combineFieldNames(tempFilePrefix, fileFieldName + '[file]'),
                        'value': file.tempFileToken
                    }));
                }
            });
        };

        updateSubmissionState();

        dropzoneElement.closest('.dms-staged-form').on('dms-post-submit-success', function () {
            dropzone.destroy();
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input.date-or-time')
        .each(function () {
            var inputElement = $(this);
            var dateFormat = Dms.utilities.convertPhpDateFormatToMomentFormat(inputElement.attr('data-date-format'));
            var mode = inputElement.attr('data-mode');

            var config = {
                locale: {
                    format: dateFormat
                },
                parentEl: inputElement.closest('.date-picker-container'),
                singleDatePicker: true,
                showDropdowns: true,
                autoApply: true,
                linkedCalendars: false,
                autoUpdateInput: false
            };

            if (mode === 'date-time') {
                config.timePicker = true;
                config.timePickerSeconds = true;
            }

            if (mode === 'time') {
                config.timePicker = true;
                config.timePickerSeconds = true;
            }
            // TODO: timezoned-date-time

            inputElement.daterangepicker(config, function (date) {
                inputElement.val(date.format(dateFormat));
            });

            var picker = inputElement.data('daterangepicker');

            if (inputElement.val()) {
                picker.setStartDate(inputElement.val());
            }

            if (mode === 'time') {
                inputElement.closest('.date-picker-container').find('.calendar-table').hide();
            }
        });

    element.find('.date-or-time-range')
        .each(function () {
            var rangeElement = $(this);
            var startInput = rangeElement.find('.start-input');
            var endInput = rangeElement.find('.end-input');
            var dateFormat = Dms.utilities.convertPhpDateFormatToMomentFormat(startInput.attr('data-date-format'));
            var mode = rangeElement.attr('data-mode');

            var config = {
                locale: {
                    format: dateFormat
                },
                parentEl: rangeElement,
                showDropdowns: true,
                autoApply: !rangeElement.attr('data-dont-auto-apply'),
                linkedCalendars: false,
                autoUpdateInput: false
            };

            if (mode === 'date-time') {
                config.timePicker = true;
                config.timePickerSeconds = true;
            }

            if (mode === 'time') {
                config.timePicker = true;
                config.timePickerSeconds = true;
            }
            // TODO: timezoned-date-time

            startInput.daterangepicker(config, function (start, end, label) {
                startInput.val(start.format(dateFormat));
                endInput.val(end.format(dateFormat));
                rangeElement.triggerHandler('dms-range-updated');
            });

            var picker = startInput.data('daterangepicker');

            if (startInput.val()) {
                picker.setStartDate(startInput.val());
            }
            if (endInput.val()) {
                picker.setEndDate(endInput.val());
            }

            endInput.on('focus click', function () {
                startInput.focus();
            });

            if (mode === 'time') {
                rangeElement.find('.calendar-table').hide();
            }
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
    element.find('.dms-inner-module, .dms-display-inner-module').each(function () {
        var innerModule = $(this);
        var fieldName = innerModule.attr('data-name');
        var rootUrl = innerModule.attr('data-root-url');
        var reloadStateUrl = rootUrl + '/state';
        var innerModuleFormContainer = innerModule.find('.dms-inner-module-form-container');
        var innerModuleForm = innerModuleFormContainer.find('.dms-inner-module-form');
        var formStage = innerModule.closest('.dms-form-stage');
        var stagedForm = innerModule.closest('.dms-staged-form');
        var currentValue = JSON.parse(innerModule.attr('data-value') || '[]');

        if (innerModule.attr('data-readonly')) {
            innerModule.find(':input').attr('readonly', 'readonly');
        }

        var getDependentData = function () {
            if (!formStage.is('.dms-dependent-form-stage')) {
                return Dms.ajax.createFormData();
            }

            var stageToDependentFieldsMap = JSON.parse(stagedForm.attr('data-stage-dependent-fields-stage-map'));
            var dependentFieldsSelector = Dms.form.stages.makeDependentFieldSelectorForStageMap(stageToDependentFieldsMap, '*');

            return Dms.form.stages.createFormDataFromFields(stagedForm.find(dependentFieldsSelector));
        };

        var fieldDataPrefix = '__field_action_data';
        var interceptor;

        Dms.ajax.interceptors.push(interceptor = {
            accepts: function (options) {
                return options.url.indexOf(rootUrl) === 0 && options.url !== reloadStateUrl;
            },
            before: function (options) {
                var formData = getDependentData();
                formData.append(fieldDataPrefix + '[current_state]', JSON.stringify(currentValue));
                formData.append(fieldDataPrefix + '[request][url]', options.url.substring(rootUrl.length));
                formData.append(fieldDataPrefix + '[request][method]', options.__emulatedType || options.type || 'get');

                var parametersPrefix = fieldDataPrefix + '[request][parameters]';
                $.each(Dms.ajax.parseData(options.data), function (name, entries) {
                    $.each(entries, function (index, entry) {
                        formData.append(Dms.utilities.combineFieldNames(parametersPrefix, name), entry.value, entry.filename);
                    });
                });

                options.__originalDataType = options.dataType;
                options.dataType = 'json';
                if ((options.type || 'get').toLowerCase() === 'get') {
                    options.data = formData.toQueryString();
                } else {
                    options.processData = false;
                    options.contentType = false;
                    options.data = formData;
                }
            },
            after: function (options, response, data) {
                if (data) {
                    currentValue = data['new_state'];

                    return Dms.ajax.convertResponse(options.__originalDataType, data.response);
                } else {
                    data = JSON.parse(response.responseText);
                    currentValue = data['new_state'];

                    response.responseText = data.response;
                    console.log(response.responseText);
                }
            }
        });

        var originalResponseHandler = Dms.action.responseHandler;
        Dms.action.responseHandler = function (httpStatusCode, actionUrl, response) {
            if (actionUrl.indexOf(rootUrl) !== 0 || httpStatusCode >= 400) {
                originalResponseHandler(httpStatusCode, actionUrl, response);
                return;
            }

            if (response.redirect) {
                var redirectUrl = response.redirect;
                delete response.redirect;

                if (!Dms.utilities.areUrlsEqual(redirectUrl, rootUrl)) {
                    loadModulePage(redirectUrl);
                }
            }

            originalResponseHandler(httpStatusCode, actionUrl, response);

            innerModule.find('.dms-table-control .dms-table').triggerHandler('dms-load-table-data');
            innerModuleForm.empty();
        };

        var rootActionUrl = rootUrl + '/action/';
        var currentAjaxRequest;

        var loadModulePage = function (url) {
            innerModuleFormContainer.addClass('loading');

            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            currentAjaxRequest = Dms.ajax.createRequest({
                url: url,
                type: 'post',
                __emulatedType: 'get',
                dataType: 'html',
                data: {'__content_only': 1}
            });

            currentAjaxRequest.done(function (html) {
                innerModuleForm.html(html);
                Dms.form.initialize(innerModuleForm);
            });

            currentAjaxRequest.fail(function () {
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

                swal({
                    title: "Could not load form",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });

            currentAjaxRequest.always(function () {
                innerModuleFormContainer.removeClass('loading');
                currentAjaxRequest = null;
            });
        };

        innerModule.on('click', 'a[href^="' + rootActionUrl + '"]', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var link = $(this);

            loadModulePage(link.attr('href'));
        });

        innerModule.closest('.form-group').on('dms-get-input-data', function () {
            var fieldData = {};
            fieldData[fieldName] = currentValue;
            return fieldData;
        });

        stagedForm.on('dms-post-submit-success', function () {
            Dms.ajax.interceptors.splice(Dms.ajax.interceptors.indexOf(interceptor), 1);
            Dms.action.responseHandler = originalResponseHandler;
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('ul.dms-field-list').each(function () {
        var listOfFields = $(this);
        var form = listOfFields.closest('.dms-staged-form');
        var formGroup = listOfFields.closest('.form-group');
        var templateField = listOfFields.find('.field-list-template');
        var addButton = listOfFields.find('.btn-add-field');
        var isInvalidating = false;

        var minFields = listOfFields.attr('data-min-elements');
        var maxFields = listOfFields.attr('data-max-elements');

        var getAmountOfInputs = function () {
            return listOfFields.children('.field-list-item').length;
        };

        var invalidateControl = function () {
            if (isInvalidating) {
                return;
            }

            isInvalidating = true;

            var amountOfInputs = getAmountOfInputs();

            addButton.prop('disabled', amountOfInputs >= maxFields);
            listOfFields.find('.btn-remove-field').prop('disabled', amountOfInputs <= minFields);

            while (amountOfInputs < minFields) {
                addNewField();
                amountOfInputs++;
            }

            isInvalidating = false;
        };

        var addNewField = function () {
            var newField = templateField.clone()
                .removeClass('field-list-template')
                .removeClass('hidden')
                .removeClass('dms-form-no-submit')
                .addClass('field-list-item');

            var fieldInputElement = newField.find('.field-list-input');
            fieldInputElement.html(fieldInputElement.text());

            var currentIndex = getAmountOfInputs();

            $.each(['name', 'data-name', 'data-field-name'], function (index, attr) {
                fieldInputElement.find('[' + attr + '*="::index::"]').each(function () {
                    $(this).attr(attr, $(this).attr(attr).replace('::index::', currentIndex));
                });
            });

            addButton.closest('.field-list-add').before(newField);

            Dms.form.initialize(fieldInputElement);
            form.triggerHandler('dms-form-updated');

            invalidateControl();
        };

        listOfFields.on('click', '.btn-remove-field', function () {
            var field = $(this).closest('.field-list-item');
            field.remove();
            formGroup.trigger('dms-change');
            from.triggerHandler('dms-form-updated');

            invalidateControl();
            // TODO: reindex
        });

        addButton.on('click', addNewField);

        invalidateControl();

        var requiresAnExactAmountOfFields = typeof minFields !== 'undefined' && minFields === maxFields;
        if (requiresAnExactAmountOfFields && getAmountOfInputs() == minFields) {
            addButton.closest('.field-list-add').remove();
            listOfFields.find('.btn-remove-field').closest('.field-list-button-container').remove();
            listOfFields.find('.field-list-input').removeClass('col-xs-10 col-md-11').addClass('col-xs-12');
        }
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    var disableZoomScrollingUntilHoveredFor = function (milliseconds, googleMap) {
        googleMap.set('scrollwheel', false);
        var timeout;
        $(googleMap.getDiv()).hover(function () {
                timeout = setTimeout(function () {
                    googleMap.set('scrollwheel', true);
                }, milliseconds);
            },
            function () {
                clearTimeout(timeout);
                googleMap.set('scrollwheel', false);
            });
    };

    element.find('.dms-map-input').each(function () {
        var mapInput = $(this);

        var latitudeInput = mapInput.find('input.dms-lat-input');
        var longitudeInput = mapInput.find('input.dms-lng-input');
        var currentLocationButton = mapInput.find('.dms-current-location');
        var fullAddressInput = mapInput.find('input.dms-full-address-input');
        var addressSearchInput = mapInput.find('input.dms-address-search');
        var mapCanvas = mapInput.find('.dms-map-picker');
        var forceSetAddress = false;

        var addressPicker = new AddressPicker({
            regionBias: 'AUS',
            map: {
                id: mapCanvas.get(0),
                zoom: 4,
                center: new google.maps.LatLng(
                    latitudeInput.val() || mapInput.attr('data-default-latitude') || -26.4390917,
                    longitudeInput.val() || mapInput.attr('data-default-longitude') || 133.281323), // Default to australia
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                draggable: !(mapCanvas.attr('data-no-touch-drag') && Dms.utilities.isTouchDevice())
            },
            marker: {
                draggable: true,
                visible: true
            },
            reverseGeocoding: true,
            autocompleteService: {
                autocompleteService: {
                    types: ['(cities)', '(regions)', 'geocode', 'establishment']
                }
            }
        });
        mapCanvas.data('map-api', addressPicker.getGMap());

        addressSearchInput.typeahead(null, {
            displayKey: 'description',
            source: addressPicker.ttAdapter()
        });

        addressSearchInput.bind("typeahead:selected", addressPicker.updateMap);
        addressSearchInput.bind("typeahead:cursorchanged", addressPicker.updateMap);
        addressPicker.bindDefaultTypeaheadEvent(addressSearchInput);

        $(addressPicker).on('addresspicker:selected', function (event, result) {
            if (!forceSetAddress && addressSearchInput.val() === '') {
                addressSearchInput.typeahead('val', '');
                latitudeInput.val('');
                longitudeInput.val('');
                fullAddressInput.val('');
                return;
            }

            forceSetAddress = false;

            if (addressSearchInput.is('[data-map-zoom]')) {
                addressPicker.getGMap().setCenter(new google.maps.LatLng(result.lat(), result.lng()));
                addressPicker.getGMap().setZoom(parseInt(addressSearchInput.attr('data-map-zoom'), 10));
            }
            latitudeInput.val(result.lat());
            longitudeInput.val(result.lng());
            var address = result.address();

            if (result.placeResult.name) {
                address = result.placeResult.name + ', ' + address;
            }

            addressSearchInput.val(address);
            fullAddressInput.val(address);
        });

        google.maps.event.addListener(addressPicker.getGMarker(), "dragend", function (event) {
            forceSetAddress = true;
        });

        if (navigator.geolocation) {
            currentLocationButton.click(function () {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var location = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    addressPicker.getGMarker().setPosition(location);
                    addressPicker.getGMap().setCenter(location);
                    // Trigger reverse geocode
                    forceSetAddress = true;
                    addressPicker.markerDragged();
                    addressPicker.getGMap().setZoom(12);
                });
            });
        } else {
            currentLocationButton.prop('disabled', true);
        }

        if (latitudeInput.val() || longitudeInput.val()) {
            forceSetAddress = true;
            addressPicker.markerDragged();
        }

        addressSearchInput.change(function () {
            addressPicker.markerDragged();
        });

        disableZoomScrollingUntilHoveredFor(1000, addressPicker.getGMap());

        google.maps.event.addListenerOnce(addressPicker.getGMap(), 'idle', function(){
            if (fullAddressInput.val()) {
                addressSearchInput.typeahead('val', fullAddressInput.val());
            }
        });
    });

    $('.dms-display-map').each(function () {
        var mapCanvas = $(this);

        var location = new google.maps.LatLng(mapCanvas.attr('data-latitude'), mapCanvas.attr('data-longitude'));
        var map = new google.maps.Map(mapCanvas.get(0), {
            center: location,
            zoom: parseInt(mapCanvas.attr('data-zoom'), 10) || 14,
            scrollwheel: false
        });

        disableZoomScrollingUntilHoveredFor(1000, map);

        mapCanvas.data('map-api', map);

        var marker = new google.maps.Marker({
            position: location,
            map: map,
            title: mapCanvas.attr('data-title')
        });
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

    element.find('input[type="number"]').each(function () {
        if ($(this).attr('data-decimal-number')) {
            $(this).attr({
                'type': $(this).attr('step') ? 'number' : 'text',
                'data-parsley-type': 'number'
            });
        } else {
            $(this).attr({
                'data-parsley-type': 'integer'
            });
        }
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    element.find('input[type=radio]').iCheck({
        radioClass: 'iradio_square-blue',
        increaseArea: '20%'
    });
});
Dms.form.initializeCallbacks.push(function (element) {

});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('table.dms-field-table').each(function () {
        var tableOfFields = $(this);
        var form = tableOfFields.closest('.dms-staged-form');
        var formGroup = tableOfFields.closest('.form-group');

        var columnFieldTemplate = tableOfFields.find('.field-column-template');
        var rowFieldTemplate = tableOfFields.find('.field-row-template');
        var cellFieldTemplate = tableOfFields.find('.field-cell-template');
        var removeRowTemplate = tableOfFields.find('.remove-row-template');
        var removeColumnTemplate = tableOfFields.find('.remove-column-template');

        var addColumnButton = tableOfFields.find('.btn-add-column');
        var addRowButton = tableOfFields.find('.btn-add-row');

        var hasPredefinedColumns = tableOfFields.attr('data-has-predefined-columns');
        var hasPredefinedRows = tableOfFields.attr('data-has-predefined-rows');
        var hasRowField = tableOfFields.attr('data-has-row-field');

        var isInvalidating = false;

        var minColumns = tableOfFields.attr('data-min-columns') || 1;
        var maxColumns = tableOfFields.attr('data-max-columns');

        var minRows = tableOfFields.attr('data-min-rows');
        var maxRows = tableOfFields.attr('data-max-rows');

        var getAmountOfColumns = function () {
            return tableOfFields.find('thead .table-column').length;
        };

        var getAmountOfRows = function () {
            return tableOfFields.find('tbody .table-row').length;
        };

        var invalidateControl = function () {
            if (isInvalidating) {
                return;
            }

            isInvalidating = true;

            var amountOfColumns = getAmountOfColumns();
            var amountOfRows = getAmountOfRows();

            addColumnButton.prop('disabled', amountOfColumns >= maxColumns);
            tableOfFields.find('.btn-remove-column').prop('disabled', amountOfColumns <= minColumns);

            while (amountOfColumns < minColumns) {
                addNewColumn();
                amountOfColumns++;
            }

            addRowButton.prop('disabled', amountOfRows >= maxRows);
            tableOfFields.find('.btn-remove-row').prop('disabled', amountOfRows <= minRows);

            while (amountOfRows < minRows) {
                addNewRow();
                amountOfRows++;
            }

            isInvalidating = false;
        };

        var createNewCell = function (columnIndex, rowIndex) {
            var newCell = cellFieldTemplate.clone().removeClass('field-cell-template');

            newCell.html(newCell.text());

            $.each(['name', 'data-name', 'data-field-name'], function (index, attr) {
                newCell.find('[' + attr + '*="::column::"]').each(function () {
                    $(this).attr(attr, $(this).attr(attr).replace('::column::', columnIndex));
                });

                newCell.find('[' + attr + '*="::row::"]').each(function () {
                    $(this).attr(attr, $(this).attr(attr).replace('::row::', rowIndex));
                });
            });

            return newCell;
        };

        var addNewColumn = function () {
            var newColumnHeader = columnFieldTemplate.clone().removeClass('field-column-template');

            var fieldContent = newColumnHeader.find('.field-content');
            fieldContent.html(fieldContent.text());

            var currentRow = 0;
            var currentColumn = getAmountOfColumns();

            $.each(['name', 'data-name', 'data-field-name'], function (index, attr) {
                newColumnHeader.find('[' + attr + '*="::column::"]').each(function () {
                    $(this).attr(attr, $(this).attr(attr).replace('::column::', currentColumn));
                });
            });

            var elementsToInit = $(newColumnHeader);

            addColumnButton.closest('.add-column').before(newColumnHeader);

            tableOfFields.find('tr.table-row').each(function (index, row) {
                var newCell = createNewCell(currentColumn, currentRow);

                $(row).find('.add-column').before(newCell);
                elementsToInit.add(newCell);

                currentRow++;
            });

            tableOfFields.find('.add-row .add-column').before(removeColumnTemplate.clone().removeClass('remove-column-button'));

            Dms.form.initialize(elementsToInit);

            form.triggerHandler('dms-form-updated');

            invalidateControl();
        };

        var addNewRow = function () {
            var currentRow = getAmountOfRows();
            var currentColumn = 0;
            var newRow = $('<tr/>').addClass('table-row');

            if (hasRowField) {
                var newRowHeader = rowFieldTemplate.clone().removeClass('field-row-template');

                var fieldContent = newRowHeader.find('.field-content');
                fieldContent.html(fieldContent.text());

                $.each(['name', 'data-name', 'data-field-name'], function (index, attr) {
                    newRowHeader.find('[' + attr + '*="::row::"]').each(function () {
                        $(this).attr(attr, $(this).attr(attr).replace('::row::', currentRow));
                    });
                });

                newRow.append(newRowHeader);
            }

            var amountOfColumns = getAmountOfColumns();
            for (currentColumn = 0; currentColumn < amountOfColumns; currentColumn++) {
                newRow.append(createNewCell(currentColumn, currentRow));
            }

            newRow.append(removeRowTemplate.clone().removeClass('remove-row-template'));

            tableOfFields.find('tr.add-row').before(newRow);

            Dms.form.initialize(newRow);

            form.triggerHandler('dms-form-updated');

            invalidateControl();
        };

        tableOfFields.on('click', '.btn-remove-column', function () {
            var parentCell = $(this).closest('td, th');
            var columnIndex = parentCell.prevAll('td, th').length;
            tableOfFields.find('tr').each(function () {
                $(this).find('td:not(.add-column), th:not(.add-column)').eq(columnIndex).remove();
            });
            parentCell.remove();

            formGroup.trigger('dms-change');
            form.triggerHandler('dms-form-updated');

            invalidateControl();
            // TODO: reindex
        });

        tableOfFields.on('click', '.btn-remove-row', function () {
            $(this).closest('tr').remove();

            formGroup.trigger('dms-change');
            form.triggerHandler('dms-form-updated');

            invalidateControl();
            // TODO: reindex
        });

        addColumnButton.on('click', addNewColumn);
        addRowButton.on('click', addNewRow);

        invalidateControl();

        var requiresAnExactAmountOfColumns = typeof minColumns !== 'undefined' && minColumns === maxColumns;
        var requiresAnExactAmountOfRows = typeof minRows !== 'undefined' && minRows === maxRows;

        if (hasPredefinedColumns || (requiresAnExactAmountOfColumns && getAmountOfColumns() == minColumns)) {
            addColumnButton.remove();
            tableOfFields.find('.btn-remove-column').remove();
            tableOfFields.find('.btn-add-column').remove();
        }

        if (hasPredefinedRows || (requiresAnExactAmountOfRows && getAmountOfRows() == minRows)) {
            addRowButton.remove();
            tableOfFields.find('.btn-remove-row').remove();
            tableOfFields.find('.btn-add-row').remove();
        }
    });
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
        var fieldLabel = $(this).children('.dms-label-container label[data-for]');
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

    element.find('.dms-staged-form').each(function () {
        var form = $(this);
        var parsley = form.parsley(window.ParsleyConfig);
        var stageElements = form.find('.dms-form-stage');

        var arePreviousFieldsValid = function (fields) {
            var originalScroll = $(document).scrollTop();
            var focusedElement = $(document.activeElement);
            parsley.validate();
            focusedElement.focus();
            $(document).scrollTop(originalScroll);

            return fields.closest('.form-group').find('.dms-validation-message *').length === 0;
        };

        stageElements.filter('.dms-dependent-form-stage').each(function () {
            var currentStage = $(this);
            var container = currentStage.closest('.dms-form-stage-container');
            var previousStages = container.prevAll('.dms-form-stage-container').find('.dms-form-stage');
            var loadStageUrl = currentStage.attr('data-load-stage-url');
            var dependentFields = currentStage.attr('data-stage-dependent-fields');
            var dependentFieldNames = dependentFields ? JSON.parse(dependentFields) : null;
            var currentAjaxRequest = null;
            var previousLoadAttempt = 0;
            var minMillisecondsBetweenLoads = 2000;
            var isWaitingForNextLoadAttempt = false;

            var makeDependentFieldSelectorFor = function (selector) {
                return Dms.form.stages.makeDependentFieldSelectorFor(dependentFieldNames, selector);
            };

            var loadNextStage = function () {

                if (currentAjaxRequest) {
                    currentAjaxRequest.abort();
                }

                container.removeClass('loaded');
                container.addClass('loading');

                var currentTime = new Date().getTime();
                var millisecondsBetweenLastLoad = currentTime - previousLoadAttempt;

                if (millisecondsBetweenLastLoad >= minMillisecondsBetweenLoads) {
                    isWaitingForNextLoadAttempt = false;
                    previousLoadAttempt = currentTime;
                }
                else {
                    if (!isWaitingForNextLoadAttempt) {
                        isWaitingForNextLoadAttempt = true;
                        setTimeout(loadNextStage, minMillisecondsBetweenLoads - millisecondsBetweenLastLoad);
                    }
                    return;
                }

                var previousFields = previousStages.find(makeDependentFieldSelectorFor('*'));

                if (!arePreviousFieldsValid(previousFields)) {
                    container.removeClass('loading');
                    return;
                }

                Dms.form.validation.clearMessages(form);

                var formData = Dms.form.stages.createFormDataFromFields(previousFields);

                currentAjaxRequest = Dms.ajax.createRequest({
                    url: loadStageUrl,
                    type: 'post',
                    processData: false,
                    contentType: false,
                    dataType: 'html',
                    data: formData
                });

                currentAjaxRequest.done(function (html) {
                    container.addClass('loaded');
                    var currentValues = currentStage.getValues(true);
                    currentStage.html(html);
                    Dms.form.initialize(currentStage);
                    currentStage.restoreValues(currentValues);
                    form.triggerHandler('dms-form-updated');
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

                currentAjaxRequest.always(function () {
                    container.removeClass('loading');
                });
            };

            previousStages.on('input', makeDependentFieldSelectorFor('input'), loadNextStage);
            previousStages.on('input', makeDependentFieldSelectorFor('textarea'), loadNextStage);
            previousStages.on('change', makeDependentFieldSelectorFor('select'), loadNextStage);

            if (dependentFieldNames) {
                var selectors = [];
                $.each(dependentFieldNames, function (index, fieldName) {
                    selectors.push('.form-group[data-field-name="' + fieldName + '"]');
                });

                previousStages.on('dms-change', selectors.join(','), loadNextStage);
            } else {
                previousStages.on('dms-change', '.form-group[data-field-name]', loadNextStage);
            }
        });
    });
});
Dms.form.initializeCallbacks.push(function (element) {

    element.find('.dms-staged-form, .dms-run-action-form').each(function () {
        var form = $(this);
        var formContainer = form.closest('.dms-staged-form-container');
        var parsley = form.parsley(window.ParsleyConfig);
        var afterRunCallbacks = [];
        var submitButtons = form.find('input[type=submit], button[type=submit]');
        var submitMethod = form.attr('data-method');
        var submitUrl = form.attr('data-action');
        var reloadFormUrl = form.attr('data-reload-form-url');

        var isFormValid = function () {
            return parsley.isValid()
                && form.find('.dms-validation-message *').length === 0
                && form.find('.dms-form-stage-container').length === form.find('.dms-form-stage-container.loaded').length;
        };

        submitButtons.on('click before-confirmation', function (e) {
            parsley.validate();

            if (!isFormValid()) {
                e.stopImmediatePropagation();
                form.find('.dms-form-stage-container:not(.loaded)').addClass('has-error');
                return false;
            }
        });

        submitButtons.on('click', function (e) {
            e.preventDefault();

            Dms.form.validation.clearMessages(form);

            form.triggerHandler('dms-before-submit');

            var fieldsToReappend = [];
            form.find('.dms-form-no-submit').each(function () {
                var removedFields = $(this).children().detach();

                fieldsToReappend.push({
                    parentElement: $(this),
                    children: removedFields
                });
            });

            var formData =  Dms.form.stages.createFormDataFromFields(form.find(':input'));
            form.find('.form-group').each(function () {
                var additionalDataToSubmit = $(this).triggerHandler('dms-get-input-data');

                if (additionalDataToSubmit) {
                    $.each(Dms.ajax.parseData(additionalDataToSubmit), function (name, entries) {
                        $.each(entries, function (index, entry) {
                            formData.append(name, entry.value, entry.filename);
                        });
                    });
                }
            });

            $.each(fieldsToReappend, function (index, elements) {
                elements.parentElement.append(elements.children);
            });

            submitButtons.prop('disabled', true);
            submitButtons.addClass('ladda-button').attr('data-style', 'expand-right');
            var ladda = Ladda.create(submitButtons.get(0));
            ladda.start();

            var currentAjaxRequest = Dms.ajax.createRequest({
                url: submitUrl,
                type: submitMethod,
                processData: false,
                contentType: false,
                dataType: 'json',
                data: formData,
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();

                    if (form.find('input[type=file]').length && xhr.upload) {
                        xhr.upload.addEventListener('progress', function (event) {
                            if (event.lengthComputable) {
                                ladda.setProgress(event.loaded / event.total);
                            }
                        }, false);
                    }

                    return xhr;
                }
            });

            currentAjaxRequest.done(function (data, statusText, xhr) {
                Dms.action.responseHandler(xhr.status, submitUrl, data);
                $.each(afterRunCallbacks, function (index, callback) {
                    callback(data);
                });

                form.triggerHandler('dms-post-submit-success');
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

                    default:
                        try {
                            var response = JSON.parse(xhr.responseText);
                            Dms.action.responseHandler(xhr.status, submitUrl, response);
                        } catch (e) {
                            // Unknown error
                            swal({
                                title: "Could not submit form",
                                text: "An unexpected error occurred",
                                type: "error"
                            });
                            break;
                        }
                }
            });

            currentAjaxRequest.always(function () {
                submitButtons.prop('disabled', false);
                ladda.stop();
            });

            return false;
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

        afterRunCallbacks.push(function (data) {
            if (data.redirect) {
                return;
            }

            var request = Dms.ajax.createRequest({
                url: reloadFormUrl,
                type: 'get',
                dataType: 'html',
                data: {'__content_only': '1'}
            });

            formContainer.addClass('loading');

            request.done(function (html) {
                var newForm = $(html).find('.dms-staged-form').first();
                form.replaceWith(newForm);
                Dms.form.initialize(newForm.parent());
            });

            request.always(function () {
                formContainer.removeClass('loading');
            });
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

    element.find('.dms-staged-form').each(function () {
        var form = $(this);
        form.parsley(window.ParsleyConfig);

        form.find('.dms-form-fields').each(function (index) {
            $(this).find(':input').attr('data-parsley-group', 'validation-group-' + index);
        });
    });

    element.find('.dms-form').each(function () {
        $(this).parsley(window.ParsleyConfig);
    });
});
Dms.form.initializeCallbacks.push(function (element) {
    if (typeof tinymce === 'undefined') {
        return;
    }

    tinymce.init({
        selector: 'textarea.dms-wysiwyg',
        tooltip: '',
        plugins: [
            "advlist",
            "autolink",
            "lists",
            "link",
            "image",
            "charmap",
            "print",
            "preview",
            "anchor",
            "searchreplace",
            "visualblocks",
            "code",
            "insertdatetime",
            "media",
            "table",
            "contextmenu",
            "paste",
            "imagetools"
        ],
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image"
    });

    element.closest('.dms-staged-form').on('dms-before-submit', function () {
        tinymce.triggerSave();
    });

    element.find('textarea.dms-wysiwyg').filter(function () {
        return $(this).closest('.mce-tinymce').length === 0;
    }).each(function () {
        if (!$(this).attr('id')) {
            $(this).attr('id', Dms.utilities.idGenerator());
        }

        tinymce.EditorManager.execCommand('mceAddEditor', true, $(this).attr('id'));
    });
});
Dms.table.initializeCallbacks.push(function (element) {
    var groupCounter = 0;

    element.find('.dms-table-body-sortable').each(function () {
        var tableBody = $(this);
        var control = tableBody.closest('.dms-table-control');
        var reorderRowsUrl = control.attr('data-reorder-row-action-url');

        var performReorder = function (event) {
            var newIndex = typeof event.newIndex === 'undefined' ? event.oldIndex : event.newIndex;

            var criteria = control.data('dms-table-criteria');
            var row = $(event.item);
            var objectId = row.find('.dms-row-action-column').attr('data-object-id');
            var reorderButtonHandle = row.find('.dms-drag-handle');

            var reorderRequest = Dms.ajax.createRequest({
                url: reorderRowsUrl,
                type: 'post',
                dataType: 'html',
                data: {
                    object: objectId,
                    index: criteria.offset + newIndex + 1
                }
            });

            if (reorderButtonHandle.is('button')) {
                reorderButtonHandle.addClass('ladda-button').attr('data-style', 'expand-right');
                var ladda = Ladda.create(reorderButtonHandle.get(0));
                ladda.start();

                reorderRequest.always(ladda.stop)
            }

            reorderRequest.fail(function () {
                swal({
                    title: "Could not reorder item",
                    text: "An unexpected error occurred",
                    type: "error"
                });
            });
        };

        var sortable = new Sortable(tableBody.get(0), {
            group: "sortable-group" + groupCounter++,
            sort: true,  // sorting inside list
            animation: 150,  // ms, animation speed moving items when sorting, `0` — without animation
            handle: ".dms-drag-handle",  // Drag handle selector within list items
            draggable: "tr",  // Specifies which items inside the element should be sortable
            ghostClass: "sortable-ghost",  // Class name for the drop placeholder
            chosenClass: "sortable-chosen",  // Class name for the chosen item
            dataIdAttr: 'data-id',

            onEnd: performReorder

        });
    });
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
            if (currentAjaxRequest) {
                currentAjaxRequest.abort();
            }

            tableContainer.addClass('loading');

            criteria.offset = currentPage * criteria.max_rows;

            currentAjaxRequest = Dms.ajax.createRequest({
                url: loadRowsUrl,
                type: 'post',
                dataType: 'html',
                data: criteria
            });

            currentAjaxRequest.done(function (tableData) {
                table.html(tableData);
                Dms.table.initialize(table);
                Dms.form.initialize(table);

                control.data('dms-table-criteria', criteria);
                control.attr('data-has-loaded-table-data', true);

                if (table.find('tbody tr').length < criteria.max_rows) {
                    paginationNextButton.addClass('disabled');
                }
            });

            currentAjaxRequest.fail(function () {
                if (currentAjaxRequest.statusText === 'abort') {
                    return;
                }

                tableContainer.addClass('has-error');

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
            var orderByComponent = filterForm.find('[name=component]').val();

            if (orderByComponent) {
                criteria.orderings = [
                    {
                        component: orderByComponent,
                        direction: filterForm.find('[name=direction]').val()
                    }
                ];
            } else {
                criteria.orderings = [];
            }

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
            currentPage--;
            paginationNextButton.removeClass('disabled');
            paginationPreviousButton.toggleClass('disabled', currentPage === 0);
            loadCurrentPage();
        });

        paginationNextButton.click(function () {
            currentPage++;
            paginationPreviousButton.removeClass('disabled');
            loadCurrentPage();
        });

        paginationPreviousButton.addClass('disabled');

        if (table.is(':visible')) {
            loadCurrentPage();
        }

        table.on('dms-load-table-data', loadCurrentPage);
    });

    $('.dms-table-tabs').each(function () {
        var tabs = $(this);

        tabs.find('.dms-table-tab-show-button').on('click', function () {
            var linkedTablePane = $($(this).attr('href'));

            linkedTablePane.find('.dms-table-control:not([data-has-loaded-table-data]) .dms-table-container:not(.loading) .dms-table').triggerHandler('dms-load-table-data');
        });
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
Dms.table.initializeCallbacks.push(function (element) {
    element.find('.dms-file-tree').each(function () {
        var fileTree = $(this);
        var filterForm = fileTree.find('.dms-quick-filter-form');
        var reloadFileTreeUrl = fileTree.attr('data-reload-file-tree-url');

        var initializeFileTreeData = function (fileTreeData) {
            var folderItems = fileTreeData.find('.dms-folder-item');
            var fileItems = fileTreeData.find('.dms-file-item');

            fileTree.find('.dms-folder-item').on('click', function (e) {
                if ($(e.target).is('.dms-file-item, .dms-file-item *')) {
                    return;
                }

                e.stopImmediatePropagation();
                $(this).toggleClass('dms-folder-closed');
            });

            filterForm.find('input[name=filter]').on('change input', function () {
                var filterBy = $(this).val();

                folderItems.hide().addClass('.dms-folder-closed');
                fileItems.each(function (index, fileItem) {
                    fileItem = $(fileItem);
                    var label = fileItem.text();

                    var doesContainFilter = label.toLowerCase().indexOf(filterBy.toLowerCase()) !== -1;
                    fileItem.toggle(doesContainFilter);

                    if (doesContainFilter) {
                        fileItem.parents('.dms-folder-item').removeClass('dms-folder-closed').show();
                    }
                });
            });
        };

        element.find('.dms-upload-form .dms-staged-form').on('dms-post-submit-success', function () {
            var fileTreeContainer = fileTree.find('.dms-file-tree-data-container');

            var request = Dms.ajax.createRequest({
                url: reloadFileTreeUrl,
                type: 'get',
                dataType: 'html',
                data: {'__content_only': '1'}
            });

            fileTreeContainer.addClass('loading');

            request.done(function (html) {
                var newFileTree = $(html).find('.dms-file-tree-data').first();
                fileTree.find('.dms-file-tree-data').replaceWith(newFileTree);
                initializeFileTreeData(newFileTree.parent());
                Dms.form.initialize(newFileTree.parent());
            });

            request.always(function () {
                fileTreeContainer.removeClass('loading');
            });
        });

        initializeFileTreeData(fileTree.find('.dms-file-tree-data'));
    });
});

//# sourceMappingURL=app.js.map
