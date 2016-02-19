<script>
    window.Dms.config = {
        routes: {
            downloadFile: function (token) {
                return {!! json_encode(route('dms::file.download', ['__token__'])) !!}.replace('__token__', token);
            }
        }
    };
</script>