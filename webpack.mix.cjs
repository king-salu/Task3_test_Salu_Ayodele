const mix = require('laravel-mix');
const Dotenv = require('dotenv-webpack');
// const dotenv = require('dotenv-webpack');

// dotenv.config({ path: '.env.react' });

mix.js('resources/js/app.jsx', 'public/js');
// .sass('resources/css/app.css', 'public/css');

mix.webpackConfig({
    module: {
        rules: [{
            test: /\.jsx?$/, exclude: /node_modules/, use: {
                loader: 'babel-loader', options: { presets: ['@babel/preset-env', '@babel/preset-react'] }
            }
        }]
    },
    resolve: {
        extensions: ['.*', '.wasm', '.mjs', '.js', '.jsx', '.json']
    },
    plugins: [
        new Dotenv({
            path: './.env.react', // Path to .env.react file 
            safe: false // Load .env.react.example to verify the .env.react variables are all set. Can also be a string to a different file. 
        })]
});