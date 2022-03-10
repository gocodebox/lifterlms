import { loadModule } from './util';
import * as data from '../../packages/data/src';

/**
 * Expose @lifterlms/accessPlans via the global `window.llms` object.
 *
 * @since [version]
 */
loadModule( 'data', data );
