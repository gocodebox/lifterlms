// Loading the Spinner component file(s) directly so that we don't load other unneeded dependencies (like WPElement/React).
import * as Spinner from '../../packages/components/src/spinner/';

/**
 * Expose the components Spinner module via the global `window.LLMS.Spinner` object for backwards compatibility.
 *
 * @since [version]
 */
window.LLMS = window.LLMS || {};
window.LLMS.Spinner = Spinner;
