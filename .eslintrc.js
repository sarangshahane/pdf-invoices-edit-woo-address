module.exports = {
  extends: ["plugin:@wordpress/eslint-plugin/recommended-with-formatting"],
  rules: {
    camelcase: "off",
    "no-console": "off",
    "no-alert": "off",
    "space-before-function-paren": "off",
    "no-mixed-spaces-and-tabs": "off",
    "eslint-plugin-jsdoc/dist/rules/informativeDocs": "off",
  },
  parserOptions: {
    requireConfigFile: false,
    babelOptions: {
      presets: ["@wordpress/babel-preset-default"],
    },
  },
  globals: {
    alert: true,
    confirm: true,
  },
};
