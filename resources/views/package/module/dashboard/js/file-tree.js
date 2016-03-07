Dms.table.initializeCallbacks.push(function (element) {
    element.find('.dms-file-tree').each(function () {
        var fileTree = $(this);
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
                    fileItem.toggle(doesContainFilter);

                    if (doesContainFilter) {
                        fileItem.parents('.dms-folder-item').removeClass('dms-folder-closed').show();
                    }
                });
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
            });

            request.always(function () {
                fileTreeContainer.removeClass('loading');
            });
        });

        initializeFileTreeData(fileTree.find('.dms-file-tree-data'));
    });
});
