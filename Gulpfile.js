var elixir = require('laravel-elixir');
var mainBowerFiles = require('main-bower-files');

var paths = {
    node: './node_modules/',
    bower: './bower_components/',
    resources: './resources/',
    dist: './dist/',
    distJs: './dist/js/',
    distCss: './dist/css/',
    distImg: './dist/img/',
    distFonts: './dist/fonts/'
};

var bowerFilesConfig = {
    paths: {
        bowerDirectory: paths.bower
    },
    overrides: {
        "admin-lte": {
            "main": [
                "dist/css/AdminLTE.css",
                "dist/css/skins/skin-blue.css",
                "dist/js/app.js"
            ],
            "dependencies": {
                "jquery": ">=2.0"
            }
        },
        "bootstrap": {
            "main": [
                "dist/css/bootstrap.css",
                "dist/js/bootstrap.js"
            ]
        },
        "parsley": {
            "main": [
                "dist/parsley.js",
                "src/extra/validator/comparison.js"
            ]
        },
        "font-awesome": {
            "main": ["css/font-awesome.css", "fonts/fontawesome-webfont.woff", "fonts/fontawesome-webfont.woff2"]
        },
        "iCheck": {
            "main": ["icheck.min.js", "skins/square/blue.css"]
        },
        "Sortable": {
            "main": ["Sortable.js"]
        },
        "JavaScript-Canvas-to-Blob": {
            "main": ["js/canvas-to-blob.min.js"]
        },
        "typeahead.js": {
            "main": ["dist/typeahead.bundle.min.js"]
        },
        "typeahead-addresspicker": {
            "main": ["dist/typeahead-addresspicker.min.js"]
        },
        "Download-File-JS": {
            "main": ["dist/download.min.js"]
        }
    }
};

var loadBowerFiles = function (filter) {
    bowerFilesConfig.filter = filter;

    return mainBowerFiles(bowerFilesConfig);
};

elixir(function (mix) {
    mix.sass([
        paths.resources + 'sass/main.scss',
        paths.resources + 'views/**/*.scss'
    ], paths.distCss + 'app.css');

    mix.scripts([
        paths.resources + 'js/main.js',
        paths.resources + 'js/services/**/*.js',
        paths.resources + 'views/**/*.js'
    ], paths.distJs + 'app.js');

    mix.copy(paths.resources + 'img/', paths.dist + 'img/');

    // Bower deps
    mix.styles(loadBowerFiles('**/*.css'), paths.distCss + 'vendor.css');
    mix.scripts(loadBowerFiles('**/*.js'), paths.distJs + 'vendor.js');
    mix.copy(loadBowerFiles(["**/*.woff", "**/*.woff2"]), paths.distFonts);
    mix.copy(paths.bower + 'admin-lte/dist/img/', paths.distImg);
    mix.copy([paths.bower + 'iCheck/skins/square/blue.png', paths.bower + 'iCheck/skins/square/blue@2x.png'], paths.distCss);

    // Wysiwyg
    var wysiwygScripts = [
        paths.bower + 'tinymce/tinymce.min.js',
        paths.bower + 'tinymce/themes/modern/theme.min.js',
    ];
    var plugins = [
        "advlist", "autolink", "lists", "link", "image", "charmap",
        "print", "preview", "anchor", "searchreplace", "visualblocks",
        "code", "insertdatetime", "media", "table", "contextmenu", "paste", "imagetools"
    ];
    for (var pluginIndex in plugins) {
        wysiwygScripts.push(paths.bower + 'tinymce/plugins/' + plugins[pluginIndex] + '/plugin.min.js');
    }
    mix.scripts(wysiwygScripts, paths.dist + 'wysiwyg/wysiwyg.js', './');
    mix.styles([paths.bower + 'tinymce/skins/lightgray/skin.min.css'], paths.dist + 'wysiwyg/wysiwyg.css');
    mix.copy([paths.bower + 'tinymce/skins/lightgray/'], paths.dist + '/wysiwyg/skins/lightgray/');
    mix.copy([paths.bower + 'tinymce/skins/lightgray/fonts'], paths.dist + '/wysiwyg/fonts/');
    mix.copy([paths.bower + 'tinymce/skins/lightgray/img'], paths.dist + '/wysiwyg/img/');

    // Combine
    mix.styles([paths.distCss + 'vendor.css', paths.distCss + 'app.css'], paths.distCss + 'all.css');
    mix.scripts([paths.distJs + 'vendor.js', paths.distJs + 'app.js'], paths.distJs + 'all.js');
});