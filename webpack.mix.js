const mix = require('laravel-mix');
let tailwindcss = require('tailwindcss');
//mix.js('resources/js/app.js', 'public/js');

const Dotenv = require('dotenv-webpack');
mix.js('resources/js/main.js', 'public/js')
  .sass('resources/sass/app.sass', 'public/css/app.css')
  .options({
    processCssUrls: false,
    postCss: [tailwindcss('./tailwind.config.js')],
  })
.webpackConfig({
  plugins: [
    new Dotenv({
      path: path.resolve(__dirname, './.env')
    })
  ]
});

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
// const tailwindcss = require('tailwindcss')

// mix.postCss('resources/css/app.css', 'public/css', [
//   require('tailwindcss'),
// ])

// // mix.sass('resources/sass/app.scss', 'public/css')
// //   .options({
// //     processCssUrls: false,
// //     postCss: [ tailwindcss('./tailwind.config.js') ],
// //   })
