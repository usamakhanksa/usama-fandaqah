let mix = require('laravel-mix')
// var SocialSharing = require('vue-social-sharing');
// Vue.use(SocialSharing);
mix.setPublicPath('dist')
    .js('resources/js/tool.js', 'js')
    .sass('resources/sass/tool.scss', 'css')
