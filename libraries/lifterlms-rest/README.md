LifterLMS REST API
==================

[![Test PHPUnit][img-phpunit-tests]][link-phpunit-tests]
[![PHP Tests Coverage][img-gh-tests-coverage]][link-gh-tests-coverage]
[![PHPCS Coding Standards][img-phpcs-checks]][link-phpcs-checks]
[![Code Climate maintainability][img-cc-maintainability]][link-cc]
[![Code Climate test coverage][img-cc-coverage]][link-cc-coverage]

[![PHP Supported Version][img-php]][link-php]

[![Contributions Welcome][img-contributions-welcome]][link-contributing]
[![Slack community][img-slack]][link-slack]

---

A REST API feature plugin for [LifterLMS](https://github.com/gocodebox/lifterlms).

---

**This specification (and repository) is in beta. It is not yet a fully-functional API. API changes will be continue to be made without deprecation until 1.0.0 is released as a "stable" API.**


## Contributing [![Contributions Welcome][img-contributions-welcome]][link-contributing]

We are looking for both API specification designers and developers interested in contributing. Read our contribution guidelines [here][link-contributing].


## Specification & Documentation

The LifterLMS REST API follows the [OpenAPI Specification (Version 3.0.0)][link-openapi-spec].

REST API documentation is available at [gocodebox.github.io/lifterlms-rest/](https://gocodebox.github.io/lifterlms-rest/).

The full OpenAPI spec can be downloaded in [json](https://gocodebox.github.io/lifterlms-rest/openapi.json) or [yaml](https://gocodebox.github.io/lifterlms-rest/openapi.yaml) formats.


## Building & Developing REST API Doc spec

This repo uses [ReDoc](https://github.com/Rebilly/ReDoc).

To build the docs locally for development:

+ `npm start`: Starts the development server.
+ `npm run build`: Bundles the spec and prepares web_deploy folder with static assets.
+ `npm test`: Validates the spec.
+ `npm run gh-pages`: Deploys docs to GitHub Pages. You don't need to run it manually if you have Travis CI configured.


## Tests and Coding Standards

The LifterLMS REST API adheres to the [documentation](links-cs-docs) and [coding standards][link-cs-code] defined for the LifterLMS Core codebase.

+ `composer run check-cs`: Check coding and documentation standards, showing warnings and errors.
+ `composer run check-cs-errors`: Check coding and documentation standards, showing errors only.

To run the phpunit test suite:

+ `composer run tests-install`: Install the test suite.
+ `composer run tests-run`: Run the test suite.


## Building and Publishing Releases

+ `llms-dev log:write`: Write changelog.
+ `llms-dev ver:update`: Update version numbers.
+ `npm run build`: Build a release: spec, doc code snippets, and included language files.
+ `llms-dev archive`: Build distributable zip file.
+ `llms-dev publish:gh`: Publish release.
+ Open a Pull Request in the LifterLMS Core to upgrade the library.

These steps require `write` access to the repository as well as access to the internal development CLI `llms-dev`. Developers and maintainers are provided with required permissions as needed.


<!-- References: Links -->
[link-cc]: https://codeclimate.com/github/gocodebox/lifterlms-rest/maintainability "LifterLMS REST on Code Climate"
[link-cc-coverage]: https://codeclimate.com/github/gocodebox/lifterlms-rest/test_coverage "Code coverage reports on Code Climate"
[link-cs-code]: https://github.com/gocodebox/lifterlms/blob/master/docs/coding-standards.md "LifterLMS Coding Standards"
[link-cs-docs]: https://github.com/gocodebox/lifterlms/blob/master/docs/documentation-standards.md "LifterLMS Documentation Standards"
[link-contributing]: https://github.com/gocodebox/lifterlms/blob/master/.github/CONTRIBUTING.md "Contribute to LifterLMS REST"
[link-openapi-spec]: https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md "OpenAPI Specification (Version 3.0.0)"
[link-php]: https://www.php.net/supported-versions "PHP Supported Versions"
[link-slack]: https://lifterlms.com/slack "Chat with the community on Slack"
[link-support]: https://lifterlms.com/my-account/my-tickets "LifterLMS customer support"
[link-support-forums]: https://wordpress.org/support/plugin/lifterlms "LifterLMS user support forums"
[link-phpunit-tests]: https://github.com/gocodebox/lifterlms-rest/actions/workflows/test-phpunit.yml "PHPUnit Tests Status"
[link-gh-tests-coverage]: https://github.com/gocodebox/lifterlms-rest/actions/workflows/php-test-coverage.yml "PHP Tests Coverage"
[link-phpcs-checks]: https://github.com/gocodebox/lifterlms-rest/actions/workflows/coding-standards.yml "PHPCS Coding Standards Checks"

[img-cc-coverage]:https://img.shields.io/codeclimate/coverage/gocodebox/lifterlms-rest?style=for-the-badge&logo=code-climate
[img-cc-maintainability]:https://img.shields.io/codeclimate/maintainability/gocodebox/lifterlms-rest?logo=code-climate&style=for-the-badge
[img-contributions-welcome]: https://img.shields.io/badge/contributions-welcome-blue.svg?style=for-the-badge&logo=data:image/svg%2bxml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPHN2ZyB3aWR0aD0iMTc5MiIgaGVpZ2h0PSIxNzkyIiB2aWV3Qm94PSIwIDAgMTc5MiAxNzkyIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik02NzIgMTQ3MnEwLTQwLTI4LTY4dC02OC0yOC02OCAyOC0yOCA2OCAyOCA2OCA2OCAyOCA2OC0yOCAyOC02OHptMC0xMTUycTAtNDAtMjgtNjh0LTY4LTI4LTY4IDI4LTI4IDY4IDI4IDY4IDY4IDI4IDY4LTI4IDI4LTY4em02NDAgMTI4cTAtNDAtMjgtNjh0LTY4LTI4LTY4IDI4LTI4IDY4IDI4IDY4IDY4IDI4IDY4LTI4IDI4LTY4em05NiAwcTAgNTItMjYgOTYuNXQtNzAgNjkuNXEtMiAyODctMjI2IDQxNC02NyAzOC0yMDMgODEtMTI4IDQwLTE2OS41IDcxdC00MS41IDEwMHYyNnE0NCAyNSA3MCA2OS41dDI2IDk2LjVxMCA4MC01NiAxMzZ0LTEzNiA1Ni0xMzYtNTYtNTYtMTM2cTAtNTIgMjYtOTYuNXQ3MC02OS41di04MjBxLTQ0LTI1LTcwLTY5LjV0LTI2LTk2LjVxMC04MCA1Ni0xMzZ0MTM2LTU2IDEzNiA1NiA1NiAxMzZxMCA1Mi0yNiA5Ni41dC03MCA2OS41djQ5N3E1NC0yNiAxNTQtNTcgNTUtMTcgODcuNS0yOS41dDcwLjUtMzEgNTktMzkuNSA0MC41LTUxIDI4LTY5LjUgOC41LTkxLjVxLTQ0LTI1LTcwLTY5LjV0LTI2LTk2LjVxMC04MCA1Ni0xMzZ0MTM2LTU2IDEzNiA1NiA1NiAxMzZ6IiBmaWxsPSIjZmZmIi8+PC9zdmc+
[img-php]: https://img.shields.io/badge/PHP-7.2%2B-brightgreen?style=for-the-badge&logoColor=white&logo=php
[img-slack]: https://img.shields.io/badge/chat-on%20slack-blueviolet?style=for-the-badge&logo=slack
[img-phpunit-tests]: https://img.shields.io/github/workflow/status/gocodebox/lifterlms-rest/Test%20PHPUnit?label=PHPUnit&logo=github&style=for-the-badge
[img-gh-tests-coverage]: https://img.shields.io/github/workflow/status/gocodebox/lifterlms-rest/PHP%20Code%20Coverage%20Report?label=PHP%20Tests%20Coverage&logo=github&style=for-the-badge
[img-phpcs-checks]:  https://img.shields.io/github/workflow/status/gocodebox/lifterlms-rest/Coding%20Standards?label=PHPCS&logo=github&style=for-the-badge
