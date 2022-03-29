const { DefaultReporter } = require( '@jest/reporters' );

class LLMSReporter extends DefaultReporter {

	async onTestResult( test, testResult, aggregatedResults ) {

		console.log( '====================' );
		console.log( '====================' );

		console.log( 'test' );
		console.log( test );

		console.log( '====================' );
		console.log( '====================' );

		console.log( 'testResult' );
		console.log( testResult );

		console.log( '====================' );
		console.log( '====================' );

		console.log( 'aggregatedResults' );
		console.log( aggregatedResults );

		console.log( '====================' );
		console.log( '====================' );

		await super.onTestResult( test, testResult, aggregatedResults );

	}

}

module.exports = LLMSReporter;
