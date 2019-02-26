let mix = require('laravel-mix');

/*
|--------------------------------------------------------------------------
| Sass
|--------------------------------------------------------------------------
*/

mix.sass('resources/sass/app.scss', 'public/css');

/*
|--------------------------------------------------------------------------
| JavaScript
|--------------------------------------------------------------------------
*/

mix.js('resources/js/bootstrap.js', 'public/js');

/*
|--------------------------------------------------------------------------
| Configure
|--------------------------------------------------------------------------
*/

mix.setPublicPath('public');

mix.options({
    processCssUrls: false,
});
