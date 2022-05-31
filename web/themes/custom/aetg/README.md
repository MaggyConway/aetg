# Base Drupal theme with Webpack/without UI-Pattern

## Requirements

- Node >= v8.x
- Yarn >= v1.1 | NPM >= v5.0

## Install dependencies

```
$ yarn
or
$ npm install
```
## How to use

You must place this folder in the custom themes folder of your Drupal installation in a sub folder named "webpack-base-theme".
Don't forget to replace `everywhere` "webpack-base-theme" to your theme name.


## Linters

There are two configs for stylelint. Default is `.stylelintrc` wich is used by IDE's stylelint to highlight quality mistakes. Another config is `.stylintrc-format` extends from default config and also includes rules for code formating based on unified convention. Also there are [prettier](https://prettier.io) to help formating code. ESLing + Prettier are used for JS linting and fixing.

## Available tasks

```sh

# Runs development server (Webpack dev server)
$ yarn dev

# Build command
$ yarn build

# Lint scss
$ yarn lint:scssf

# Lint js
$ yarn lint:js

# Lint all
$ yarn lint

# Format scss
$ yarn format:scss

# Format js
$ yarn format:js

# Format all
$ yarn format

# Runs http-server on port 8082
$ yarn httpserver

```

### All aliases

```
assets:    '../src/assets/'
styles:    '../src/styles/'
scripts:   '../src/scripts/'
mixins:    '../src/styles/settings/mixins.scss'
variables: `../src/styles/settings/variables.scss'
```

All common parts will be splitted into `./dist/vendors/`