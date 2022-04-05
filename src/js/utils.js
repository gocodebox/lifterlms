import loadModule from './util/load-module';
import * as Utils from '../../packages/utils/src';

/**
 * Expose @lifterlms/utils via the global `window.llms` object.
 *
 * @since 6.0.0
 * @since [version] Use `loadModule()` to load the module.
 */
loadModule( 'utils', Utils );
