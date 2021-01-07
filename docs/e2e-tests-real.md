Running E2E (End to End) Tests Against a Real Website
=====================================================

_The core E2E test suite is primarily designed to be run locally against managed Docker containers. However, it is possible to run the test suite against any WordPress website with a publicly accessible URL by following this guide._

_To run tests locally against managed Docker containers, see the [E2E Testing README](../tests/e2e/README.md)._

**NOTE: This is an experimental process! Proceed with caution. We are developing this process for internal use and thought it might be useful to some other folks.**

**Another note: This process will import courses, create fake users, and add other data to your website and there is no cleanup proccess. If you choose to use this against a live production site that means that the database will have a bunch of fake test data added to it. So don't run this against a real production website. Use a staging website instead!**

## Prerequisites

+ Ability to use a terminal
+ git
+ node.js
+ npm


## Setup your local environment

+ Install the LifterLMS repo: `git clone https://github.com/gocodebox/lifterlms`
+ Move into the cloned directory: `cd liferlms`
+ Install node packages: `npm ci`
+ Create a new file in the created directory named `.llmsenv`.
+ Use your favorite text editor to edit the file and add the following to the file (replacing the example data with your site's information):

```
WP_BASE_URL=https://yourwebsiteurl.tld
WP_USERNAME=adminusername
WP_PASSWORD=adminpassword
```

**This will store a password in a PLAIN TEXT which we know is wrong. Our internal use case uses this process with temporary sites which are regularly destroyed so the danger is acceptable to our use case. If you decide to use this process on a real website with real user information you have been warned that storing your production site's WP admin password in a plain text file in order to use this process is a bad idea. We recommend instead using environment variables to pass your password to the script later and removing the WP_PASSWORD from the `.llmsenv` file.**

+ Save the file


## Setup your production site

+ Install and activate the LifterLMS plugin on your site


## Run the tests

There are two ways to run the E2E tests:

### Headless mode

Runs the tests and shows you the results.

If errors are encountered, a screenshot of the page will be taken and saved in the `tmp/e2e-screenshots/` directory so you can see what the page looked like when things went sour.

Error logs will be output in your terminal to review.

Run headless tests by executing `npm run tests` in your terminal.


### Interactive mode

Launches an automated Chromium browser and runs the tests in "slow motion" so you can watch as the tests run.

No screenshots are takeng in interactive mode.

Error logs are output to the terminal for review.

Run interactive tests by executing `npm run tests:dev` in your terminal.


### Using environment variables

If you don't want to store you admin password in a plaintext file you can define the WP_PASSWORD variable at runtime `WP_PASSWORD=yourpassword npm run tests`
