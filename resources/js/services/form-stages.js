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
            selectors.push('.dms-form-stage[data-stage-number="' + stageNumber + '"] ' +  selector + ':input');
        } else {
            var fieldsInStageSelector = Dms.form.stages.makeDependentFieldSelectorFor(
                dependentFields,
                '.dms-form-stage[data-stage-number="' + stageNumber + '"] ' + selector,
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

Dms.form.stages.getDependentDataForStage = function (formStage) {
    var stagedForm = formStage.closest('.dms-staged-form');
    
    if (!formStage.is('.dms-dependent-form-stage')) {
        return Dms.ajax.createFormData();
    }

    var stageToDependentFieldsMap = JSON.parse(formStage.attr('data-stage-dependent-fields-stage-map'));
    var dependentFieldsSelector = Dms.form.stages.makeDependentFieldSelectorForStageMap(stageToDependentFieldsMap, '*');

    return Dms.form.stages.createFormDataFromFields(stagedForm.find(dependentFieldsSelector));
};