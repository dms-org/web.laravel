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
            "main": ["fonts/fontawesome-webfont.woff", "fonts/fontawesome-webfont.woff2"]
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
    ], paths.distCss + 'app.css', './');

    mix.scripts([
        paths.resources + 'js/main.js',
        paths.resources + 'js/services/**/*.js',
        paths.resources + 'views/**/*.js'
    ], paths.distJs + 'app.js', './');

    mix.copy(paths.resources + 'img/', paths.dist + 'img/');

    // Bower deps
    mix.styles(loadBowerFiles('**/*.css'), paths.distCss + 'vendor.css', './');
    mix.scripts(loadBowerFiles('**/*.js'), paths.distJs + 'vendor.js', './');
    mix.copy(loadBowerFiles(["**/*.woff", "**/*.woff2"]), paths.distFonts);
    mix.copy(paths.bower + 'admin-lte/dist/img/', paths.distImg);

    // Combine
    mix.styles([paths.distCss + 'vendor.css', paths.distCss + 'app.css'], paths.distCss + 'all.css', './');
    mix.scripts([paths.distJs + 'vendor.js', paths.distJs + 'app.js'], paths.distJs + 'all.js', './');
});