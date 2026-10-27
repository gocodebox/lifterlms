LifterLMS REST API
==================

REST API for [LifterLMS](https://github.com/gocodebox/lifterlms) (`/wp-json/llms/v1/`).

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin. The old [gocodebox/lifterlms-rest](https://github.com/gocodebox/lifterlms-rest) repository is archived.

Define `LLMS_REST_DISABLE` as true before LifterLMS loads to skip loading it.

Integrator documentation: https://developer.lifterlms.com/

## OpenAPI specification

The spec lives in [`spec/`](spec/) and follows the [OpenAPI Specification 3.0.0](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.0.md).

Rendered docs: https://gocodebox.github.io/lifterlms-rest/

From this directory:

+ `npm start` serves the spec locally.
+ `npm test` validates the spec.
+ `npm run build:docs` builds the static docs.

## Contributing

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md), including the [coding](../../docs/coding-standards.md) and [documentation](../../docs/documentation-standards.md) standards. Changelog entries go in the core `.changelogs/` directory. PHPUnit coverage is in `tests/phpunit/unit-tests/libraries/lifterlms-rest/`. Run the core suite from the repository root; see [`tests/phpunit/README.md`](../../tests/phpunit/README.md).
