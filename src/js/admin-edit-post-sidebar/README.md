LifterLMS Admin Edit Post Sidebar
=================================

A block editor edit post sidebar plugin for use by LifterLMS and LifterLMS add-ons.

This sidebar is automatically registered on course and membership post types.

If required on other post types, it can be added as a dependency to any script using the handle `llms-admin-edit-post-sidebar` or manually enqueued:

```php
add_action( 'admin_enqueue_scripts', function() {
  llms()->assets->enqueue_script( 'llms-admin-edit-post-sidebar' );
} );
```

## Usage

Additional controls and content can be rendered into the sidebar using the Slot, for example:

```jsx
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { registerPlugin } from '@wordpress/plugins';

const { Fill } = window.llms.editPostSidebar;

registerPlugin(
  'llms-access-plan-editor',
  {
    render: () => (
      <Fill>
        <PanelBody title="{ __( 'My Settings Panel', 'my-domain' ) }">
          <div>My panel content.</div>
        </PanelBody>
      </Fill>
    )
  }
);
```
