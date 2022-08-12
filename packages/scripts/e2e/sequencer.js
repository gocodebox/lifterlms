/**
 * Jest tests sequencer
 *
 * Runs our tests in alphabetical order by directory / filename.
 *
 * This allows us to do things like run the setup wizard tests to further bootstrap
 * the testing environment for other tests.
 *
 * @since Unknown
 * @version Unknown
 *
 * @see {@link https://jestjs.io/docs/en/next/configuration#testsequencer-string}
 */

const Sequencer = require( '@jest/test-sequencer' ).default;

class CustomSequencer extends Sequencer {
	sort( tests ) {
		const copyTests = Array.from( tests );
		return copyTests.sort( ( testA, testB ) => ( testA.path > testB.path ? 1 : -1 ) );
	}
}

module.exports = CustomSequencer;
