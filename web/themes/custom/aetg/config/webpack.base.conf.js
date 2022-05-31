/* eslint-disable */
const path = require('path');

module.exports = {
  module: {
    rules: [{
      test    : /\.js?$/,
      exclude : [/node_modules/],
      loader  : 'babel-loader'
    },
    {
      test    : /\.(woff(2)?|eot|ttf|otf)$/,
      loader  : 'file-loader',
      options : {
        name       : '[name].[ext]',
        publicPath: '../fonts/',
        outputPath : 'fonts/'
      }
    }]
  },

  resolve: {
    alias: {
      assets: path.resolve(__dirname, '../src/assets/'),
      styles: path.resolve(__dirname, '../src/styles/'),
      scripts: path.resolve(__dirname, '../src/scripts/'),
      mixins: path.resolve(__dirname, '../src/styles/settings/mixins.scss'),
      variables: path.resolve(__dirname, '../src/styles/settings/variables.scss')
    }
  },

  externals: {
    Drupal: 'Drupal',
    jquery: 'jQuery',
  }
}