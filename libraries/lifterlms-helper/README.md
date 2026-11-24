LifterLMS Helper
================

Updates, installs, and beta-tests LifterLMS add-ons.

This directory is part of the LifterLMS core repository and ships with LifterLMS. It is not a standalone plugin. The old [gocodebox/lifterlms-helper](https://github.com/gocodebox/lifterlms-helper) repository is archived.

Define `LLMS_HELPER_DISABLE` as true before LifterLMS loads to skip loading it.

## License status and notices

`llms_helper_get_license_status()` returns the site's license status:

+ `active`: at least one license key is saved.
+ `lapsed`: no key is saved, and LifterLMS.com reported a previously saved key as no longer valid.
+ `none`: no key is saved and none is known to have lapsed.

`llms_helper_get_unlicensed_add_ons()` returns the active add-ons that require a license and are not covered by a saved key. Pass `false` to include the ones that are installed and inactive.

A site running unlicensed add-ons sees one dismissible notice on LifterLMS screens, the Dashboard, and the Plugins, Themes, and Updates screens. The notice removes itself once every active add-on is licensed.

+ `license-lapsed`: the license lapsed. Shown once it has been lapsed for a week; filter the delay with `llms_helper_lapsed_license_notice_delay`.
+ `license-missing`: no license lapsed. Shown right away.

Filter `llms_helper_license_notice` to change which notice is shown. Return an empty string to show none.

On the Plugins screen, each installed add-on without a license, active or not, also has a message under its row with links to the Add-ons screen, to enter a license key, and to the LifterLMS.com pricing page, to purchase a license. It replaces the "unlicensed and cannot be updated" message that used to appear only when an update was available.

### Banner notifications feed

The Helper adds two tests to the `show_if` and `hide_if` rules of the [banner notifications](../banner-notifications/README.md) feed:

+ `llms_license`: a license status, or a list of statuses, to match.
+ `llms_unlicensed_add_ons`: `true` to match sites running unlicensed add-ons, `false` to match the others.

A banner for free sites that have no license and run no paid add-ons:

```json
"show_if": { "llms_license": "none" },
"hide_if": { "llms_unlicensed_add_ons": true }
```

LifterLMS versions without these tests never match them, so they do not show a banner that requires one in `show_if`. A test in `hide_if` hides nothing on them.

## Development

Styles compile from `assets/scss/llms-helper.scss`. From the LifterLMS repository root:

```bash
npm run build --workspace=lifterlms-helper
```

`npm run build:libraries` builds this package and Blocks together. From this directory, `npm run watch:styles` rebuilds the expanded stylesheet as you edit.

## Contributing

Follow the [LifterLMS core contribution guidelines](../../.github/CONTRIBUTING.md). Changelog entries go in the core `.changelogs/` directory. PHPUnit coverage is in `tests/phpunit/unit-tests/libraries/lifterlms-helper/`.
