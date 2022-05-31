module.exports = {
  extends: ["airbnb-base", "plugin:prettier/recommended"],
  plugins: ["prettier", "import"],
  parser: 'babel-eslint',
  env: {
    browser: true,
    es6: true,
    node: true
  },
  globals: {
    PRODUCTION: true,
    Drupal: true,
    drupalSettings: true,
    jQuery: true,
    scripts: true,
    utils: true,
    styles: true
  },
  rules: {
    "prettier/prettier": "error",
    "comma-dangle": ["error", "always-multiline"],
    "arrow-parens": ["error", "as-needed"],
    "no-console": 0
  },
  settings: {
    'import/resolver': {
      alias: {
        map: [
          ['scripts', './src/scripts/']
        ],
        extensions: ['.js', '.jsx', '.json']
      }
    }
  },
  root: true
}
