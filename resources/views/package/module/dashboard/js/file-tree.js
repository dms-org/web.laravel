Dms.global.initializeCallbacks.push(function (element) {
    element.find('.dms-file-tree').each(function () {
        var fileTree = $(this);
        var filterForm = fileTree.find('.dms-quick-filter-form');
        var folderItems = fileTree.find('.dms-folder-item');
        var fileItems = fileTree.find('.dms-file-item');

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
    });
});