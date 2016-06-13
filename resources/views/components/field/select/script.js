Dms.form.initializeCallbacks.push(function (element) {
    element.find('.dms-select-with-remote-data').each(function () {
        var control = $(this);
        var input = control.find('.dms-select-input');
        var hiddenInput = control.find('.dms-select-hidden-input');
        var formGroup = control.closest('.form-group');
        var formStage = control.closest('.dms-form-stage');

        var remoteDataUrl = control.attr('data-remote-options-url');
        var remoteMinChars = control.attr('data-remote-min-chars');

        var engine = new Bloodhound({
            datumTokenizer: function (datum) {
                return Bloodhound.tokenizers.whitespace(datum.value);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: remoteDataUrl + '?query=%QUERY',
                ajax: {
                    type: 'POST',
                    cache: false,
                    processData: false,
                    contentType: false,
                    beforeSend: function (jqXHR, settings) {
                        settings.data = Dms.form.stages.getDependentDataForStage(formStage).getNativeFormData();
                    }
                },
                filter: function (results) {
                    return results;
                }
            }
        });

        engine.initialize();

        input.typeahead(null, {
            displayKey: 'label',
            hint: true,
            highlight: true,
            minLength: remoteMinChars,
            source: engine.ttAdapter()
        }).on('typeahead:selected', function (event, data) {
            hiddenInput.val(data.val);
            formGroup.trigger('dms-change');
        });
    });
});