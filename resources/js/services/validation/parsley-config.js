window.ParsleyConfig = {
    successClass: "has-success",
    errorClass: "has-error",
    excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    classHandler: function (el) {
        return el.$element.closest(".form-group");
    },
    errorsWrapper: "<span class='help-block dms-validation-message'></span>",
    errorTemplate: "<span></span>"
};