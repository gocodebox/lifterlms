import * as Components from '../../packages/components/src';

window.llms = window.llms || {};

// Preserve components from `lifterlms-blocks`.
const { components = {} } = window.llms;

/**
 * Expose @lifterlms/components via the global `window.llms` object.
 *
 * @since 6.0.0
 */
window.llms.components = {
	...components,
	...Components,
};
