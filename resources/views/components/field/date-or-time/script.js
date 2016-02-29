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
                parentEl: inputElement.parent(),
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
                inputElement.parent().find('.calendar-table').hide();
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