<script>
    window.Dms.config = {
        csrf: {
            token: {!! json_encode(csrf_token()) !!}
        },
        routes: {
            loginUrl: {!! json_encode(route('dms::auth.login')) !!},
            localUrls: {
                root: {!! json_encode(url('/dms')) !!},
                exclude: [
                    {!! json_encode(route('dms::auth.logout')) !!}
                ]
            },
            downloadFile: function (token) {
                return {!! json_encode(route('dms::file.download', ['__token__'])) !!}.replace('__token__', token);
            }
        }
    };
</script>