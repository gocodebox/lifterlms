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
+ `composer run env up`: Build and install the local environment.
+ `composer run env:setup`: Setup the local environment.

After installation a WordPress site should be accessible at [http://localhost:8080](http://localhost:8080) using the username `admin` and password `password`.


## Running Tests

To run tests:

+ `npm run test`: Runs all tests in a headless browser.
+ `npm run test:dev`: Runs tests in an interactive browser with "slow" motion enabled. This mode is helpful when writing tests so you can see what's going on.
+ `npm run test -- -t SuiteName`: Run a single test suite by name. "SuiteName" will be the name of a test file `describe()`. For  example "SetupWizard".
+ `npm run test -- -t "test expect description"`: Run a single test by its "should" description block. For example "should load and run the entire setup wizard.".


## Managing Docker Containers

The local environment is powered by docker containers which can be managed with the following commands:

```
config:  Creates configuration override files
down:    Stop and remove containers and volumes
up:      Start containers
ps:      List containers
reset:   Destroy and recreate containers and volumes
restart: Restart containers
rm:      Remove containers and volumes
ssh:     Open an interactive bash session with the PHP service container
stop:    Stop containers without removing them
wp:      Execute a wp-cli command inside the PHP service container
```

To run these commands, run `composer run env <command>` where `<command>` is the name of the command you wish to run.

For additionally information and options for each command run, the command with the `-h` or `--help` flag to view usage information.


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
+ [llms-e2e-test-utils](https://github.com/gocodebox/lifterlms/tree/trunk/packages/llms-e2e-test-utils): End-To-End (E2E) test utils for LifterLMS.

A debt of gratitude is owed to [WP React Starter by devowl.io](https://github.com/devowlio/wp-react-starter), without the open-source code found in this repository our lead developer would surely have descended into eventual madness trying to figure out how to mount a working directory into a Docker container. I know you're saying it sounds simple and in retrospect he agrees with you but you know how things go sometimes...
