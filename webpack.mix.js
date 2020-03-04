const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

var adminLayoutJsList = [
    'resources/js/admin/jquery.min.js',
    'resources/js/admin/bootstrap.bundle.min.js',
    'resources/js/admin/jquery.easing.min.js',
    'resources/js/admin/sb-admin-2.min.js',
];

//------admin
mix.styles(['resources/css/*.css', 'resources/css/admin/*.css'], 'public/css/admin/app.css') //admin layout css
    .scripts(adminLayoutJsList, 'public/js/admin/app.js') //admin layout js

    //------common
    .js('resources/js/croppie/croppie.js', 'public/js/croppie/croppie.js') //croppie js
    .sass('resources/css/croppie/croppie.scss', 'public/css/croppie/croppie.css') //croppie css

