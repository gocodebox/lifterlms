# Block Development

Below are the steps for creating and registering a new block for LifterLMS.

Please note that before beginning you will need to have Node and NPM installed on your machine. Please see [https://github.com/gocodebox/lifterlms/blob/trunk/docs/installing.md](https://github.com/gocodebox/lifterlms/blob/trunk/docs/installing.md) for installation details.

#### Table of Contents
- [1. Create block files](#1-create-block-files)
- [2. Add block JSON data](#2-add-block-json-data)
- [3. Adding Block JS](#3-adding-block-js)
- [4. Compiling blocks](#4-compiling-blocks)
- [5. Register with PHP](#5-register-with-php)
- [6. Block design guidelines](#6-block-design-guidelines)

### 1. Create block files

Create a new folder in the `src/blocks` directory for your block. E.g. `/src/blocks/example-block/`. Then, add a `block.json` file and an `index.jsx` file to the new folder.

The block directory structure should now look like this:

```shell
.
└── project/
    ├── src/
    │   └── blocks/
    │       └── block/
    │           ├── block.json
    │           ├── index.jsx
    │           └── index.scss # optional.
    ├── package.json
    └── webpack.congif.js
```

### 2. Add block JSON data

Next, add block information to the `block.json` file. Below is an example of a block.json file. Note that the category should be `lifterlms` to match the other LifterLMS blocks:

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 2,
  "name": "llms/example-block",
  "title": "Example",
  "category": "llms-blocks",
  "description": "Block description",
  "textdomain": "lifterlms",
  "attributes": {},
  "supports": {},
  "editorScript": "file:./index.js"
}
```

### 3. Adding Block JS

Next, add the block’s JavaScript to the `index.jsx` file. We use the JSX file extension to indicate that the file contains JSX code.

Below is an example of how to register a new block and access the block.json data to set the block’s name and attributes:

```jsx
import { registerBlockType } from '@wordpress/blocks';
import blockJson from './block.json';

registerBlockType( blockJson, {
    edit: ( props ) => {
        return <p>{ props.name }</p>;
    },
    save: ( props ) => {
        return <p>{ props.name }</p>;
    },
} );
```

*Note that while it is common practise to separate the `edit` and `save` functions into separate files, this is not necessary unless the code becomes too complex to manage in a single file. We prefer to keep the code in a single file where possible.*

### 4. Compiling blocks

To compile the block, open the Terminal and run the following NPM script from the plugin root directory. This will compile all blocks to the main `/blocks/` directory:

`npm run build:blocks`

### 5. Register with PHP

The last step is to register the block with PHP. This should be added to a PHP file or class where it makes sense. For example, shortcode blocks are registered in the `/includes/shortcodes/class.llms.shortcodes.blocks.php` file. Below is an example of how to register a block with PHP and allow WordPress to handle the loading of scripts and styles:

```php
add_action( 'init', 'llms_register_example_block' );
/**
 * Register the example block.
 *
 * @since 1.0.0
 *
 * @return void
 */
function llms_register_example_block() {
    register_block_type( LLMS_PLUGIN_DIR . 'blocks/example-block' );
}
```

### 6. Block design guidelines

Blocks should be designed to be as simple as possible.

#### Icons

Blocks should use FontAwesome icons. The complete list of icons can be found at [https://fontawesome.com/icons](https://fontawesome.com/icons). SVG icons need to be converted to React components and added to blocks with the `registerBlockType` function:

```jsx
import { registerBlockType } from '@wordpress/blocks';
import { SVG, Path } from '@wordpress/primitives';

const Icon = () => (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
		<Path
			d="M592 416H48c-26.5 0-48-21.5-48-48V144c0-26.5 21.5-48 48-48h544c26.5 0 48 21.5 48 48v224c0 26.5-21.5 48-48 48z"
		/>
	</SVG>
);

registerBlockType( blockJson, {
  icon: Icon,
  edit: Edit
} );
```

#### Colors

Blocks use the default core admin color palette. This ensures that hover and active states are consistent with other blocks.

## Shortcodes

For blocks with Server Side Rendering functionality, a shortcode should also be registered to support users who are not using the block editor. The shortcode should follow LifterLMS shortcode naming conventions and be registered in the `/includes/shortcodes/` directory and extend the `LLMS_Shortcode` class.

Shortcode blocks can use the `llms_shortcode_blocks` filter provided by the  `LLMS_Shortcode_Block` class to handle the block registration and rendering. Below is an example of how to register a block with the class from within a LifterLMS add-on plugin:

```php
add_filter( 'llms_shortcode_blocks', register_blocks( array $config ): array {
    $config['group-list'] = array(
        'render' => array( 'LLMS_Groups_Shortcode_Group_List', 'output' ),
        'path'   => LLMS_GROUPS_PLUGIN_DIR . 'assets/blocks/group-list',
    );

    return $config;
} );
```
