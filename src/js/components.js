import * as Components from '../../packages/components/src';

window.llms = window.llms || {};

// Preserve components from `lifterlms-blocks`.
const { components = {} } = window.llms;

/**
 * Expose @lifterlms/components via the global `window.llms` object.
 *
 * @since [version]
 */
window.llms.components = { ...Components };
