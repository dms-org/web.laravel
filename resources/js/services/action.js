Dms.action.responseHandler = function (response) {
    if (typeof response.redirect !== 'undefined') {
        if (typeof respoonse.messsage !== 'undefined') {
            Cookies.set('dms-flash-alert-success', response.message);
        }

        window.location.href = response.redirect;
    }

    if (typeof respoonse.messsage !== 'undefined') {
        Dms.alerts.add('success', response.message);
    }

    if (typeof response.files !== 'undefined') {
        swal({
            title: "Downloading files",
            text: "Please wait while your download begins. <br> Files: " + response.files.join(', '),
            type: "info",
            showConfirmButton: false,
            showLoaderOnConfirm: true
        });

        $.each(response.files, function (index, file) {
            $('<iframe />')
                .attr('src', Dms.config.routes.downloadFile(file.token))
                .css('display', 'none')
                .appendTo($(document.body));
        });

        var downloadsBegun = 0;
        var checkIfDownloadsHaveBegun = function () {

            $.each(response.files, function (index, file) {
                var fileCookieName = 'file-download-' + file.token;

                if (Cookies.get(fileCookieName)) {
                    downloadsBegun++;
                    Cookies.remove(fileCookieName)
                }
            });

            if (downloadsBegun < response.files.length) {
                setTimeout(checkIfDownloadsHaveBegun, 100);
            } else {
                swal.close();
            }
        };

        checkIfDownloadsHaveBegun();
    }
};