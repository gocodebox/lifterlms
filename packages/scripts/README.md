LifterLMS Scripts
=================

Test, build, and development scripts for LifterLMS projects.

This package is inspired by and extends functionality provided by [@wordpress/scripts](https://github.com/WordPress/gutenberg/tree/master/packages/scripts), adding functionality specifically for testing, building, and developing LifterLMS projects and add-ons.

## Installation

Install the module

```
npm install --save-dev @lifterlms/scripts
```

## CHANGELOG

[CHANGELOG](./CHANGELOG.md)

## Configuration Files

### ESLint Plugin

The [eslint](./config/.eslintrc.js) configuration file specifies a shared set of rules for linting Javascript files across LifterLMS projects.

The configuration is a modified version of the [@wordpress/eslint-plugin/recommended-with-formatting](https://github.com/WordPress/gutenberg/blob/trunk/packages/eslint-plugin/configs/recommended-with-formatting.js).

Example usage `.eslintrc.js`

```js
const config = require( '@lifterlms/scripts/config/.eslintrc.js' );
module.exports = config;
```
