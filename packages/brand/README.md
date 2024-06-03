LifterLMS Brand
===============

LifterLMS brand icons, colors, and more.

## Installation

Install the module

```
npm install --save @lifterlms/brand
```

## Usage

### SCSS Colors

Import LifterLMS brand colors and WordPress core admin colors:

_Note: Ensure that `node_modules` is included in your SASS load path!_

```scss
// Import all brand files.
@import '@lifterlms/brand/sass/brand'

// Import colors only.
@import '@lifterlms/brand/sass/colors'

// Import typography only.
@import '@lifterlms/brand/sass/typography'

// Use a color.
body {
  background: llms-color( llms-blue );
}

// Use the gradient mixin.
.banner {
  @include llms-gradient-bg();
}

// Use a font.
body {
  font-family: llms-font( llms-sans );
}
`
