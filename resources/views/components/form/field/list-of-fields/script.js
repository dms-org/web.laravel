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