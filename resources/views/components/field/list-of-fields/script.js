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