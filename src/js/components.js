import { loadModule } from './util';
import * as Components from '../../packages/components/src';

/**
 * Expose @lifterlms/components via the global `window.llms` object.
 *
 * @since 6.0.0
 * @since [version] Use `loadModule()` to load the module.
 */
loadModule( 'components', Components, true );
