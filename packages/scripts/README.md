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

### WordPress Blocks Webpack Configuration File

The [blocks-webpack.config.js](./config/blocks-webpack.config.js) is a Webpack config file meant to build WordPress blocks found within the the project's `src/blocks` directory. The distribution directory is `blocks/`.

The config will automatically build blocks, compile SCSS to CSS, move the block.json file, and copy all PHP files for each block in the source directory.

#### Example Usage

Create a `webpack.config.js` in your project's root with the following:

```js 
const blocksConfig = require( '@lifterlms/scripts/config/blocks-webpack.config' );
module.exports = blocksConfig;
````

#### Configuration

The configuration assumes a project directory following this structure:

```
a-plugin/
|-- a-plugin.php
|-- assets/
|   |-- css/
|   |-- js/
|-- blocks/
|   |-- block-a/
|   |-- block-b/
|-- README.md
|-- includes/
|-- src/
|   |-- blocks/
|   |   |-- block-a/
|   |   |   |-- block.json
|   |   |   |-- index.js
|   |   |   |-- styles.scss
|   |   |-- block-b/
|   |       |-- block.json
|   |       |-- index.js
|   |       |-- index.php
|   |       |-- styles.scss
|   |-- js/
|   |-- scss/
|-- webpack.config.js
```

#### Expected filenames

The script builds scripts and styles according to definitions found in the `block.json` files.

```
editorScript - index.js
viewScript   - view.js
script       - script.js
style        - styles.scss (styles.css)
editorStyle  - editor.scss (editor.css)
*.php        - *.php
```


### ESLint Plugin

The [eslint](./config/.eslintrc.js) configuration file specifies a shared set of rules for linting Javascript files across LifterLMS projects.

The configuration is a modified version of the [@wordpress/eslint-plugin/recommended-with-formatting](https://github.com/WordPress/gutenberg/blob/trunk/packages/eslint-plugin/configs/recommended-with-formatting.js).

Example usage `.eslintrc.js`

```js
const config = require( '@lifterlms/scripts/config/.eslintrc.js' );
module.exports = config;
```
