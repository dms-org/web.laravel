Dms.utilities.countDecimals = function (value) {
    if (value % 1 != 0) {
        return value.toString().split(".")[1].length;
    }

    return 0;
};

Dms.utilities.idGenerator = function () {
    var S4 = function () {
        return (((1 + Math.random()) * 0x10000) | 0).toString(16).substring(1);
    };
    return 'id' + (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4());
};

Dms.utilities.combineFieldNames = function (outer, inner) {
    if (inner.indexOf('[') === -1) {
        return outer + '[' + inner + ']';
    }

    var firstInner = inner.substring(0, inner.indexOf('['));
    var afterFirstInner = inner.substring(inner.indexOf('['));

    return outer + '[' + firstInner + ']' + afterFirstInner;
};

Dms.utilities.areUrlsEqual = function (first, second) {
    return first.replace(/\/+$/, '') === second.replace(/\/+$/, '');
};

Dms.utilities.downloadFileFromUrl = function (url) {
    $('<iframe />')
        .attr({'src': url})
        .hide()
        .appendTo('body');
};

Dms.utilities.isTouchDevice = function () {
    try {
        document.createEvent("TouchEvent");
        return true;
    } catch (e) {
        return false;
    }
};

Dms.utilities.convertPhpDateFormatToMomentFormat = function (format) {
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