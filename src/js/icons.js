import loadModule from './util/load-module';
import * as Icons from '../../packages/icons/src';

/**
 * Expose @lifterlms/icons via the global `window.llms` object.
 *
 * @since 6.0.0
 * @since [version] Use `loadModule()` to load the module.
 */
loadModule( 'icons', Icons );
