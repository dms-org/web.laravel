Dms.ajax.formData = function (form) {
    var formValues = {};
    var nativeFormData = new FormData(form);

    this.__isInternalFormData = true;

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
};

Dms.ajax.createFormData = function (form) {
    return new Dms.ajax.formData(form);
};

Dms.ajax.parseData = function (data) {
    if (data instanceof Dms.ajax.formData) {
        return data.getFormValues();
    }

    var dataMap = {};

    var queryString = $.param(data);
    $.each(queryString.split('&'), function (index, parameter) {
        var parts = parameter.split('=');

        dataMap[decodeURIComponent(parts[0])] = {value: decodeURIComponent(parts[1])};
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
                var returnValue =  interceptor.after(response, data);

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

