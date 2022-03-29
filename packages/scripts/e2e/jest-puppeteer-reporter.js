const { DefaultReporter } = require( '@jest/reporters' );

class LLMSReporter extends DefaultReporter {

	async onTestResult( test, testResult, aggregatedResults ) {

		console.log( testResult );

		await super.onTestResult( test, testResult, aggregatedResults );

	}

}

module.exports = LLMSReporter;
