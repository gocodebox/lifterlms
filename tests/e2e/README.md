LifterLMS E2E (End-to-End) Tests
================================

## Requirements

The E2E test suite requires [Node](https://nodejs.org/en/download/) to run tests via the terminal of your choosing.

[Docker](https://docs.docker.com/install/) is not required, but it is recommended. You could configure any WordPress site (local or publicly accessible) to be used for testing.

**If you choose to run tests on an environment other than Docker, the setup and configuration will differ from what is outlined here and you will also risk polluting your site with unwanted test content and data.**


## Installation

To install the test suite:

+ `npm install`: Install Node dependencies.
+ `composer install`: Install all required PHP dependencies.
+ `npm run env install`: Build and install the local environment.

After installation a WordPress site should be accessible at [http://localhost:8889](http://localhost:8889) using the username `admin` and password `password`.

Note that a directory `/wordpress` will be installed in the root of the repository. This directory will house the WordPress core information which mounted to the Docker environment.


## Running Tests

To run tests:

+ `npm run test`: Runs all tests in a headless browser.
+ `npm run test:dev`: Runs tests in an interactive browser with "slow" motion enabled. This mode is helpful when writing tests so you can see what's going on.
+ `npm run test -- -t SuiteName`: Run a single test suite by name. "SuiteName" will be the name of a test file `describe()`. For  example "SetupWizard".
+ `npm run test -- -t "test expect description"`: Run a single test by its "should" description block. For example "should load and run the entire setup wizard.".


## Managing Docker Containers

The local environment is powered by docker containers which can be managed with the following commands:

+ `npm run env install`: Download, build, and install WordPress locally. Automatically links the working directory (the LifterLMS plugin code) and starts the local environment.
+ `npm run env start`: Start the containers.
+ `npm run env stop`: Stop the containers.
+ `npm run env reinstall`: Reset the database and local copy of WordPress (useful to clear test data after running tests).
+ `npm run env update`: Update the local copy of WordPress.
+ `npm run env cli`: Run a `wp-cli` command against the local copy of WordPress.

For advanced commands and more information see [@wordpress/scripts docs](https://github.com/WordPress/gutenberg/tree/master/packages/scripts#available-sub-scripts).


## Test Organization

All tests are stored in the [tests/e2e/tests](./tests) directory.

Tests should organized into subdirectories by group and each file should function as a secondary level of organization for grouping tests.


## Credits

Tools and libraries used:

+ [Puppeteer](https://github.com/GoogleChrome/puppeteer): a Node library which provides a high-level API to control Chrome or Chromium over the DevTools Protocol.
+ [Jest](https://github.com/facebook/jest): A comprehensive JavaScript testing solution.
+ [jest-puppeteer](https://github.com/smooth-code/jest-puppeteer): A test runner to run tests using Jest & Puppeteer.
+ [expect-puppeteer](https://github.com/smooth-code/jest-puppeteer/tree/master/packages/expect-puppeteer): Assertion library for Puppeteer.

The following utility packages are used to help facilitate e2e tests in WordPress and LifterLMS:

+ [@wordpress/scripts](https://github.com/WordPress/gutenberg/tree/master/packages/scripts): A collection of reusable scripts tailored for WordPress development.
+ [@wordpress/e2e-test-utils](https://github.com/WordPress/gutenberg/tree/master/packages/e2e-test-utils): End-To-End (E2E) test utils for WordPress.
+ [llms-e2e-test-utils](https://github.com/gocodebox/lifterlms/tree/master/packages/llms-e2e-test-utils): End-To-End (E2E) test utils for LifterLMS.

a collection of reusable scripts tailored for WordPress development
