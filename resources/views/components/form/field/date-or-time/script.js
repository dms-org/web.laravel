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

        $.each(replacements, function (phpToken, momentToken) {
            format = format.replace(phpToken, momentToken);
        });

        return format;
    };

    element.find('input.date-or-time')
        .each(function () {
            $(this).datetimepicker({
                format: convertPhpDateFormatToMomentFormat($(this).attr('data-date-format')),
                minDate: $(this).attr('data-min-date') ? new Date($(this).attr('data-min-date')) : null,
                maxDate: $(this).attr('data-max-date') ? new Date($(this).attr('data-max-date')) : null,
                useCurrent: !$(this).attr('data-dont-use-current')
            });
        });

    element.find('.date-or-time-range')
        .each(function () {
            var startInput = $(this).find('input.date-or-time.start-input');
            var endInput = $(this).find('input.date-or-time.end-input');

            startInput.on("dp.change", function (e) {
                endInput.data("DateTimePicker").minDate(e.date);
            });

            endInput.on("dp.change", function (e) {
                startInput.data("DateTimePicker").maxDate(e.date);
            });
        });
});