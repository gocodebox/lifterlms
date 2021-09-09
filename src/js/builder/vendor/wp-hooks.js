/**
 * Load @wordpress/hooks into the `window.llms` namespace.
 *
 * The @wordpress/hooks module was originally included in this way because we introduced usage of the hooks before the package
 * was included in the WordPress core. Now that the package is guaranteed to be available we don't need to include our own version
 * but we're including it in the namespace to preserve usage via the `window.llms` namespace.
 *
 * @since 3.16.0
 * @version [version]
 */

import * as hooks from '@wordpress/hooks';

window.llms       = window.llms || {};
window.llms.hooks = hooks;
