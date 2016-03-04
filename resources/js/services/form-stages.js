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
        var fieldName = $(this).attr('name');

        if ($(this).is('[type=file]')) {
            $.each(this.files, function (index, file) {
                formData.append(fieldName, file);
            });
        } else {
            formData.append(fieldName, $(this).val());
        }
    });

    return formData;
};