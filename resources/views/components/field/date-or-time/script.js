Dms.form.initializeCallbacks.push(function (element) {
    var convertPhpDateFormatToMomentFormat = function (format) {
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

    element.find('input.date-or-time')
        .each(function () {
            var inputElement = $(this);
            var dateFormat = convertPhpDateFormatToMomentFormat(inputElement.attr('data-date-format'));
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
            var dateFormat = convertPhpDateFormatToMomentFormat(startInput.attr('data-date-format'));
            var mode = rangeElement.attr('data-mode');

            var config = {
                locale: {
                    format: dateFormat
                },
                parentEl: rangeElement,
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

            startInput.daterangepicker(config, function (start, end, label) {
                startInput.val(start.format(dateFormat));
                endInput.val(end.format(dateFormat));
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