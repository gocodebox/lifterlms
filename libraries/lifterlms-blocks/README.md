LifterLMS Blocks
================

WordPress Editor (Gutenberg) blocks for LifterLMS.

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin. The old [gocodebox/lifterlms-blocks](https://github.com/gocodebox/lifterlms-blocks) repository is archived.

Core loads it when the block editor is available. Return `false` from the `llms_load_blocks_plugin` filter to skip loading it.

## Development

Source is in `src/`. From the LifterLMS repository root:

```bash
npm run start --workspace=lifterlms-blocks
npm run build --workspace=lifterlms-blocks
```

`npm run build:libraries` builds this package and the Helper together.

## Contributing

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md). Changelog entries go in the core `.changelogs/` directory. PHPUnit coverage is in `tests/phpunit/unit-tests/libraries/lifterlms-blocks/`.
