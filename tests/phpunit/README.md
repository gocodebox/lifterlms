LifterLMS Tests
===============

## Running Tests Locally

To install tests locally you'll first need a local MySQL server (5.6 or later) and PHP 7.1. Xdebug is required to generate code coverage reports.

### Installing

1. Install all development dependencies via `composer install`
2. Install the testing database and environment: `composer run-script tests-install`

### Running Tests

+ Run tests: `composer run-script tests-run`
+ Run tests by group `composer run-script tests-run -- --group LLMS_Post_Model`
+ Run a specific tests `composer run-script tests-run -- --filter test_my_test_method`
+ Run tests and generate code coverage in HTML format: `composer run-script tests-run -- --coverage-html tmp/coverage`
+ Run tests and generate text code coverage: `composer run-script tests-run -- --coverage-text`

## Automated Testing

Tests are run automatically on commits and pull requests via [CircleCI](https://circleci.com/gh/gocodebox/lifterlms/tree/master).

## Code Coverage

Code coverage is available on [Code Climate](https://codeclimate.com/github/gocodebox/lifterlms/code?sort=-test_coverage) and updated automatically after each CircleCI build.

## Writing Tests

+ Each test file should roughly correspond to an associated source file, e.g. the `functions/class-llms-test-functions-access-plans.php` test file covers code in the `functions/llms-functions-access-plans.php` file.
+ Each test method should cover a single method or function with one or more assertions
+ A single method or function can have multiple associated test methods if it's a large or complex method
+ Use coverage reports to examine which lines your tests are covering and aim for 100% coverage
+ In addition to covering each line of a method/function, make sure to test common input and edge cases.
+ Remember that only methods prefixed with test will be run so use helper methods liberally to keep test methods small and reduce code duplication.
+ If there is a common helper method used in multiple test files, consider adding it to the `LLMS_UnitTestCase` class so it can be shared by all test cases.
+ The test suite uses the `lifterlms-tests` library which is aimed to provide shared utilities for testing the LifterLMS core, as well as LifterLMS add-ons. Many methods and utilities are available and documented in the libraries GitHub repo: https://github.com/gocodebox/lifterlms-tests
+ Filters, options, and actions persist between test cases so be sure to remove or reset them in your test method or in the `tear_down()` method.
