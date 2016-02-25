Dms.form.initializeCallbacks.push(function (element) {

    element.find('.dropzone-container').each(function () {
        var container = $(this);
        var form = container.closest('form');
        var dropzone = container.find('.dms-dropzone');
        var fieldName = container.attr('data-name');
        var required = container.attr('data-required');
        var tempFilePrefix = container.attr('data-tempfile-key-prefix');
        var existingFile = JSON.parse(container.attr('data-file'));

        var uniqueId = Dms.utilities.idGenerator();

        var action = existingFile ? 'keep-existing' : 'store-new';
        var tempFileToken = null;

        var updateSubmissionState = function () {
            form.find('#file-action-' + uniqueId).remove();
            form.find('#file-token-' + uniqueId).remove();

            form.append($('<input />').attr({
                'id': 'file-action-' + uniqueId,
                'type': 'hidden',
                'name': Dms.utilities.combineFieldNames(fieldName, 'action'),
                'value': action
            }));

            if (tempFileToken) {
                form.append($('<input />').attr({
                    'id': 'file-token-' + uniqueId,
                    'type': 'hidden',
                    'name': Dms.utilities.combineFieldNames(tempFilePrefix, fieldName + '[file]'),
                    'value': tempFileToken
                }));
            }
        };

        dropzone.attr('id', 'dropzone-' + uniqueId);
        new Dropzone('#dropzone-' + uniqueId,  {
            url: container.attr('data-upload-temp-file-url'),
            maxFilesize: container.attr('data-max-size'),
            maxFiles: 1,
            headers: Dms.utilities.getCsrfHeaders(),
            acceptedFiles: JSON.parse(container.attr('data-allowed-extensions') || '[]').map(function (extension) {
                return '.' + extension;
            }).join(','),
            init: function () {

                this.on("addedfile", function (file) {
                    var removeButton = Dropzone.createElement('<button type="button" class="btn btn-sm btn-danger"><i class="fa fa-times"></i></button>');
                    var _this = this;

                    removeButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        _this.removeFile(file);
                        tempFileToken = null;
                        action = 'delete-existing';
                        updateSubmissionState();

                        if (_this.options.maxFiles === 0) {
                            _this.options.maxFiles++;
                        }
                    });

                    file.previewElement.appendChild(removeButton);
                });

                this.on('success', function (file, response) {
                    tempFileToken = response.tokens[fieldName];
                    action = 'store-new';
                    updateSubmissionState();
                });

                if (existingFile) {
                    this.emit("addedfile", existingFile);
                    //  this.createThumbnailFromUrl(existingFile, existingFile.url);
                    this.emit("complete", existingFile);
                    this.options.maxFiles--;
                }
            }
        });

        dropzone.addClass('dropzone');
        updateSubmissionState();
    });
});