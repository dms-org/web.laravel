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