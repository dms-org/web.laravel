Dms.form.initializeCallbacks.push(function (element) {

    element.find('.list-of-checkboxes').each(function () {
        var listOfCheckboxes = $(this);

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