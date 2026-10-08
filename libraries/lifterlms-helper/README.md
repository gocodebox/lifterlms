LifterLMS Helper
================

Updates, installs, and beta-tests LifterLMS add-ons.

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin. The old [gocodebox/lifterlms-helper](https://github.com/gocodebox/lifterlms-helper) repository is archived.

Define `LLMS_HELPER_DISABLE` as true before LifterLMS loads to skip loading it.

## Development

Styles compile from `assets/scss/llms-helper.scss`. From the LifterLMS repository root:

```bash
npm run build --workspace=lifterlms-helper
```

`npm run build:libraries` builds this package and Blocks together. From this directory, `npm run watch:styles` rebuilds the expanded stylesheet as you edit.

## Contributing

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md). Changelog entries go in the core `.changelogs/` directory. PHPUnit coverage is in `tests/phpunit/unit-tests/libraries/lifterlms-helper/`.
