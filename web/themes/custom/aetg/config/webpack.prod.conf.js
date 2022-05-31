/* eslint-disable */

const path = require('path')
const webpack = require('webpack')
const merge = require('webpack-merge')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin')
const base = require('./webpack.base.conf')

const entryArray = [];

const entryObject = entryArray.reduce((acc, item) => {
  const name = `../${item.replace('/entry.js', '/dist')}`;
  acc[name] = item;
  return acc;
}, {});

const configProd = {
  mode: 'production',
  entry: {
    ...entryObject,
    main: './src/index.js',
  },
  output: {
    filename: '[name]/index.js'
  },
  optimization: {
    runtimeChunk: 'single',
    splitChunks: {
      cacheGroups: {
        vendors: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all'
        }
      }
    },
    minimizer: [
      new TerserPlugin({
        test: /\.m?js$/,
        sourceMap: false,
        terserOptions: {
          safari10: true
        }
      })
    ]
  },
  module: {
    rules: [
      {
        test: /(\.scss|\.css)$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            loader: 'css-loader',
            options: {
              sourceMap: true
            }
          },
          {
            loader: 'resolve-url-loader'
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: true
            }
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true
            }
          }
        ]
      },
      {
        test: /\.(png|jpe?g|gif|svg|webp)$/i,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: 'images/[name].[ext]',
              publicPath: (url, resourcePath, context) => {
                const relativePath = path.relative(context, resourcePath);

                return `../${url}`;
              },
              outputPath: (url, resourcePath, context) => {
                const relativePath = path.relative(context, resourcePath);

                return url;
              }
            }
          },
          {
            loader: 'img-loader',
            options: {
              plugins: [
                require('imagemin-gifsicle')({
                  interlaced: true
                }),
                require('imagemin-mozjpeg')({
                  progressive: true,
                  arithmetic: false
                }),
                require('imagemin-optipng')({
                  optimizationLevel: 5
                }),
                require('imagemin-svgo')({
                  plugins: [
                    { convertPathData: false }
                  ]
                })
              ]
            }
          }
        ]
      }
    ]
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "[name]/style.css"
    }),

    new OptimizeCSSAssetsPlugin({
      cssProcessorOptions: {
        safe: true,
        discardComments: true
      },
    }),

    new webpack.DefinePlugin({
      PRODUCTION: JSON.stringify(true)
    })
  ]
}

module.exports = merge(base, configProd)