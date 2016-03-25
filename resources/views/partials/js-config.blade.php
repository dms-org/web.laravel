<script>
    window.Dms.config = {
        routes: {
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