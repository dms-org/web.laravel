Dms.form.initializeCallbacks.push(function (element) {
    if (typeof tinymce === 'undefined') {
        return;
    }

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
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image"
    });

    element.closest('.dms-staged-form').on('dms-before-submit', function () {
        tinymce.triggerSave();
    });

    element.find('textarea.dms-wysiwyg').filter(function () {
        return $(this).closest('.mce-tinymce').length === 0;
    }).each(function () {
        if (!$(this).attr('id')) {
            $(this).attr('id', Dms.utilities.idGenerator());
        }

        tinymce.EditorManager.execCommand('mceAddEditor', true, $(this).attr('id'));
    });
});