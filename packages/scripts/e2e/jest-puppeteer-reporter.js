const { DefaultReporter } = require( '@jest/reporters' );

class LLMSReporter extends DefaultReporter {

	async onTestResult( test, testResult, aggregatedResults ) {

		console.dir( testResult, { depth: null } );

		await super.onTestResult( test, testResult, aggregatedResults );

	}

}

module.exports = LLMSReporter;
