/* jQuery.values: get or set all of the name/value pairs from child input controls
 * @argument data {array} If included, will populate all child controls.
 * @returns element if data was provided, or array of values if not
 */

$.fn.values = function(data) {
    var $els = this.find(':input');
    var els = $els.get();

    var getAbsoluteName = function (element) {
        var name = element.name;

        if (name.substr(-2) === '[]') {
            var inputsWithSameNameBefore = $els
                .filter(function (index, otherElement) {
                    return otherElement.name === name;
                })
                .filter(function (index, otherElement) {
                    var preceding = 4;
                    return otherElement.compareDocumentPosition(element) & preceding;
                });

            name = name.substr(0, name.length - 2) + '[' + inputsWithSameNameBefore.length + ']';
        }

        return name;
    };

    if(arguments.length === 0) {
        // return all data
        data = {};

        $.each(els, function() {
            if (this.name && !this.disabled && (this.checked
                || /select|textarea/i.test(this.nodeName)
                || /text|hidden|password/i.test(this.type))) {
                data[getAbsoluteName(this)] = $(this).val();
            }
        });
        return data;
    } else {

        $.each(els, function() {
            if (!this.name) {
                return;
            }

            var name = getAbsoluteName(this);

            if (data[name]) {
                var value = data[name];
                var $this = $(this);

                if(this.type == 'checkbox' || this.type == 'radio') {
                    $this.attr("checked", value === $.val());
                } else {
                    $this.val(value);
                }
            }
        });

        return this;
    }
};