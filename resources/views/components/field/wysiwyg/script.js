Dms.form.initializeCallbacks.push(function (element) {
    if (typeof tinymce === 'undefined') {
        return;
    }

    var wysiwygElements = element.find('textarea.dms-wysiwyg');

    wysiwygElements.each(function () {
        if (!$(this).attr('id')) {
            $(this).attr('id', Dms.utilities.idGenerator());
        }
    });

    tinymce.baseURL = '/vendor/dms/wysiwyg/';
    tinymce.init({
        selector: 'textarea.dms-wysiwyg',
        tooltip: '',
        plugins: [
            "advlist",
            "autolink",
            "lists",
            "link",
            "image",
            "charmap",
            "print",
            "preview",
            "anchor",
            "searchreplace",
            "visualblocks",
            "code",
            "insertdatetime",
            "media",
            "table",
            "contextmenu",
            "paste",
            "imagetools"
        ],
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image",
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
            editor.on('keyup cut paste change', function (e) {
                $(tinymce.activeEditor.getElement()).closest('.form-group').trigger('dms-change');
            });
        },
        relative_urls: false,
        remove_script_host: true,
        file_picker_callback: function (callback, value, meta) {
            var wysiwygElement = $(tinymce.activeEditor.getElement()).closest('.dms-wysiwyg-container');
            showFilePickerDialog(meta.filetype, wysiwygElement, function (fileUrl) {
                callback(fileUrl);
            });
        }
    });

    wysiwygElements.filter(function () {
        return $(this).closest('.mce-tinymce').length === 0;
    }).each(function () {
        tinymce.EditorManager.execCommand('mceAddEditor', true, $(this).attr('id'));
    });

    wysiwygElements.closest('.dms-staged-form').on('dms-post-submit-success', function () {
        $(this).find('textarea.dms-wysiwyg').each(function () {
            tinymce.remove('#' + $(this).attr('id'));
        });
    });

    var showFilePickerDialog = function (mode, wysiwygElement, callback) {
        var loadFilePickerUrl = wysiwygElement.attr('data-load-file-picker-url');
        var filePickerDialog = wysiwygElement.find('.dms-file-picker-dialog');
        var filePickerContainer = filePickerDialog.find('.dms-file-picker-container');
        var filePicker = filePickerContainer.find('.dms-file-picker');

        filePickerDialog.appendTo('body').modal('show');
        filePickerDialog.on('hidden.bs.modal', function () {
            filePickerDialog.appendTo(wysiwygElement);
        });

        var request = Dms.ajax.createRequest({
            url: loadFilePickerUrl,
            type: 'get',
            dataType: 'html',
            data: {'__content_only': '1'}
        });

        filePickerContainer.addClass('loading');

        request.done(function (html) {
            filePicker.html(html);
            Dms.table.initialize(filePicker);
            Dms.form.initialize(filePicker);

            var updateFilePickerButtons = function () {
                filePicker.find('.dms-trashed-files-btn-container').hide();
                var selectFileButton = $('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>');

                filePicker.find('.dms-file-action-buttons').each(function () {
                    var fileItemButtons = $(this);

                    var specificFileSelectButton = selectFileButton.clone();
                    fileItemButtons.empty();
                    fileItemButtons.append(specificFileSelectButton);

                    specificFileSelectButton.on('click', function () {
                        callback(fileItemButtons.closest('.dms-file-item').attr('data-public-url'));
                        filePickerDialog.modal('hide');
                    });
                });

                if (mode === 'image') {
                    filePicker.find('.btn-images-only').click().focus();
                }
            };

            filePicker.find('.dms-file-tree').on('dms-file-tree-updated', updateFilePickerButtons);
            updateFilePickerButtons();
        });

        request.always(function () {
            filePickerContainer.removeClass('loading');
        });

        filePickerDialog.on('hide.bs.modal', function () {
            filePicker.empty();
        });
    };


    element.find('.dms-display-html').each(function () {
        var control = $(this);
        var viewMoreButton = control.find('.dms-view-more-button');
        var iframe = control.find('iframe').get(0);
        var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;

        iframeDocument.body.innerHTML = control.attr('data-value');

        viewMoreButton.on('click', function () {
            Dms.controls.showContentDialog('Preview', iframeDocument.body.innerHTML, true);
        });
    });
});