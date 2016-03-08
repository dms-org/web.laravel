Dms.table.initializeCallbacks.push(function (element) {
    element.find('.dms-file-tree').each(function () {
        var fileTree = $(this);
        var fileTreeData = fileTree.find('.dms-file-tree-data');
        var filterForm = fileTree.find('.dms-quick-filter-form');
        var reloadFileTreeUrl = fileTree.attr('data-reload-file-tree-url');

        var initializeFileTreeData = function (fileTreeData) {
            var folderItems = fileTreeData.find('.dms-folder-item');
            var fileItems = fileTreeData.find('.dms-file-item');

            fileTree.find('.dms-folder-item').on('click', function (e) {
                if ($(e.target).is('.dms-file-item, .dms-file-item *')) {
                    return;
                }

                e.stopImmediatePropagation();
                $(this).toggleClass('dms-folder-closed');
            });

            filterForm.find('input[name=filter]').on('change input', function () {
                var filterBy = $(this).val();

                folderItems.hide().addClass('.dms-folder-closed');
                fileItems.each(function (index, fileItem) {
                    fileItem = $(fileItem);
                    var label = fileItem.text();

                    var doesContainFilter = label.toLowerCase().indexOf(filterBy.toLowerCase()) !== -1;
                    fileItem.toggleClass('hidden', !doesContainFilter);

                    if (doesContainFilter) {
                        fileItem.parents('.dms-folder-item').removeClass('dms-folder-closed').show();
                    }
                });

                hideEmptyFolders(fileTreeData);
            });

            hideEmptyFolders(fileTreeData);
        };

        var hideEmptyFolders = function (fileTreeData) {
            fileTreeData.find('.dms-folder-item').each(function () {
                $(this).toggle($(this).find('.dms-file-item:not(.hidden)').length > 0);
            });
        };

        element.find('.dms-upload-form .dms-staged-form').on('dms-post-submit-success', function () {
            var fileTreeContainer = fileTree.find('.dms-file-tree-data-container');

            var request = Dms.ajax.createRequest({
                url: reloadFileTreeUrl,
                type: 'get',
                dataType: 'html',
                data: {'__content_only': '1'}
            });

            fileTreeContainer.addClass('loading');

            request.done(function (html) {
                var newFileTree = $(html).find('.dms-file-tree-data').first();
                fileTree.find('.dms-file-tree-data').replaceWith(newFileTree);
                initializeFileTreeData(newFileTree.parent());
                Dms.form.initialize(newFileTree.parent());
                fileTree.triggerHandler('dms-file-tree-updated');
            });

            request.always(function () {
                fileTreeContainer.removeClass('loading');
            });
        });

        fileTree.find('.btn-images-only').on('click', function () {
            fileTreeData.find('.dms-file-item:not(.dms-image-item)').addClass('hidden');
            hideEmptyFolders(fileTreeData);
        });

        fileTree.find('.btn-all-files').on('click', function () {
            fileTreeData.find('.dms-file-item').removeClass('hidden');
            hideEmptyFolders(fileTreeData);
        });

        initializeFileTreeData(fileTreeData);
    });
});
