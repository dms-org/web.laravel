Dms.utilities.countDecimals = function (value) {
    if (value % 1 != 0) {
        return value.toString().split(".")[1].length;
    }

    return 0;
};

Dms.utilities.idGenerator = function() {
    var S4 = function() {
        return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
    };
    return 'id' + (S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());
};

Dms.utilities.combineFieldNames = function(outer, inner) {
    if (inner.indexOf('[') === -1) {
        return outer + '[' + inner + ']';
    }

    var firstInner = inner.substring(0, inner.indexOf('['));
    var afterFirstInner = inner.substring(inner.indexOf('['));

    return outer + '[' + firstInner + ']' + afterFirstInner;
};