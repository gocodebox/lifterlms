# LifterLMS Font Awesome

A wrapper around [Font Awesome](https://github.com/FortAwesome/Font-Awesome) and a collection of related React components for use in LifterLMS projects.

* * *

## Changelog

[View the Changelog](./CHANGELOG.md)

## Configure and generate CSS files

To create the relevant CSS file which includes all the free icons and necessary CSS classes, create an SCSS file:

```scss
$llms-css-prefix: my-prefix-fa;
@import '@lifterlms/fontawesome/src/fontawesome';
```

The `$llms-css-prefix` variable allows creation of the Font Awesome CSS file in a "no-conflict" mode using a different prefix than the default `fa` prefix commonly used with Font Awesome. The default prefix, `llms-fa` is used by the LifterLMS core plugin. Any other projects should choose a unique prefix in order to avoid conflicts with other plugins (or LifterLMS) which may be loading various other versions of Font Awesome.

Then add an entry to your webpack config file, if you're using `@lifterlms/scripts/config/webpack.config.js`:

```js
const { resolve } = require( 'path' ),
	config = generate( {} );

config.entry.fontawesome = resolve( './src/scss/fa-file.scss' );

module.exports = config;
```

When building you'll now find the Font Awesome CSS file at `assets/css/fa-file.scss` and the `assets/fonts` directory will contain copies of the Font Awesome font files.

## Using SVGs

The above steps enable using Font Awesome as a webfont. If you wish to instead use SVGs, you may wish to copy the SVGs to your project's directory. The [svg](./bin/svg.js) script can be used to copy the source SVGs into you project.

```bash
node ./node_modules/@lifterlms/fontawesome/bin/svg.js [destDir]    
```

The `destDir` parameter defaults to `./src/img/fontawesome` if omitted.

## Component and API Docs

<!-- START TOKEN(Autogenerated API docs) -->

### getMetadata

Retrieves metadata for a given icon.

_Parameters_

-   _iconId_ `string`: The icon ID.

_Returns_

-   `IconMeta|boolean`: An icon metadata object or `false` if the icon can't be found.

### Icon

Renders a Font Awesome icon.

_Parameters_

-   _props_ `Object`: Component properties.
-   _props.icon_ `string`: The Icon ID.
-   _props.iconStyle_ `string`: The icon style, enum: "solid", "regular", or "brands".
-   _props.iconPrefix_ `string`: The project's icon prefix.
-   _props.label_ `string`: The (optional) accessibility label to display for the icon.
-   _props.wrapperProps_ `...Object`: Any remaining properties which are passed to the icon wrapper component.

_Returns_

-   `WPElement`: The component.

### IconPicker

Renders an icon picker component, intended to be used within the WordPress block editor.

_Parameters_

-   _props_ `Object`: Component properties.
-   _props.icon_ `string`: The Icon ID.
-   _props.iconStyle_ `string`: The icon style, enum: "solid", "regular", or "brands".
-   _props.iconPrefix_ `string`: The project's icon prefix.
-   _props.controlProps_ `Object`: Properties to pass through to the <BaseControl> component.
-   _props.onChange_ `Function`: Function called when an icon is selected from the picker. The function is passed three properties: The icon ID, the currently selected style, and the icon's predefined label.

_Returns_

-   `BaseControl`: A BaseControl containing the icon picker component.


<!-- END TOKEN(Autogenerated API docs) -->
