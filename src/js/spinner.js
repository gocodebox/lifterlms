// Loading the Spinner component file(s) directly so that we don't load other unneeded dependencies (like WPElement/React).
import * as Spinner from '../../packages/components/src/spinner/';

/**
 * Expose the components Spinner module via the global `window.LLMS.Spinner` object for backwards compatibility.
 *
 * This is automatically included in the `llms.js` script file so you likely do not need to include this script directly.
 *
 * @since 7.0.0
 */
window.LLMS = window.LLMS || {};
window.LLMS.Spinner = Spinner;
