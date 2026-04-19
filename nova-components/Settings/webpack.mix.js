let mix = require('laravel-mix')

mix.setPublicPath('dist')
    .js('resources/js/tool.js', 'js')
    .sass('resources/sass/tool.scss', 'css')
    .sass('resources/sass/custom-rtl.scss', 'css')


