Dms.form.initializeCallbacks.push(function (element) {
    element.find('.dms-inner-module').each(function () {
        var innerModule = $(this);
        var rootUrl = innerModule.attr('data-root-url');
        var innerModuleForm = innerModule.find('.dms-inner-module-form');
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

        Dms.ajax.interceptors.push({
            accepts: function (options) {
                return options.url.indexOf(rootUrl) === 0;
            },
            before: function (options) {
                var formData = getDependentData();
                formData.append(fieldDataPrefix + '[current_state]', currentValue);
                formData.append(fieldDataPrefix + '[request][url]', options.url.substring(rootUrl.length));
                formData.append(fieldDataPrefix + '[request][method]', options.type || 'get');

                var parametersPrefix = fieldDataPrefix + '[request][parameters]';
                $.each(Dms.ajax.parseData(options.data), function (name, entry) {
                    formData.append(Dms.utilities.combineFieldNames(parametersPrefix, name), entry.value, entry.filename);
                });

                options.__originalDataType = options.dataType;
                options.dataType = 'json';
                options.processData = false;
                options.contentType = false;
                options.data = formData;
            },
            after: function (response, data) {
                if (data) {
                    currentValue = data['new_state'];
                    return data.response;
                } else {
                    data = JSON.parse(response.responseText);
                    currentValue = data['new_state'];
                    response.responseText = JSON.stringify(data.response);
                }
            }
        })
    });
});