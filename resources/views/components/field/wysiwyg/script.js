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

    element.find('form').on('submit', function () {
        tinymce.triggerSave();
    });
});