import * as utils from '../../packages/utils/src';

/**
 * Expose @lifterlms/utils via the global `window.llms` object.
 *
 * @since 6.0.0
 */
window.llms = window.llms || {};
window.llms.utils = utils;
