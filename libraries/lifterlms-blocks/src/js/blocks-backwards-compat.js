/**
 * Ensure backwards compatibility with WP Core packages we rely on.
 *
 * @since 2.0.0
 * @version 2.0.0
 */

// Import aliased versions of WP Core Packages.
import { createReduxStore, register } from 'llmsWPData';

// Make text definitions of all the data stores we reference via import.
window.wp.blockEditor.store = 'core/block-editor';
window.wp.editor.store = 'core/editor';
window.wp.notices.store = 'core/notices';

// Ensure we can register data stores.
window.wp.data = {
	...window.wp.data,
	createReduxStore,
	register,
};
