LifterLMS Javascript Packages
=============================

This repository uses [lerna](https://lerna.js.org/) to manage LifterLMS modules and publish them as packages to [npm](https://www.npmjs.com/).

---

## Package Changelogs

Each package is versioned independently and maintains its own changelog.

When updating packages, an update to the changelog should be included outlining the changes. This should be added to the "Unreleased" heading at the top of the changelog file. If the heading doesn't already exist it should be added.

Additionally, methods and functions keep their own changelogs (like the LifterLMS core plugin) and `[version]` placeholders should be used for unreleased changes.


## Inclusion in the LifterLMS Core Plugin

Some packages are included in the LifterLMS Core Plugin. See package details for usage instructions. Each package that is included this way maintains a table outlining the package version included in various LifterLMS versions.


## API Documentation

Where applicable, each package maintains its own independent API documentation which is published to the package's README.md file. The API documentation is automatically generated using the `docgen` script for each package.


## Scripts

Most packages should include at least a `docgen`, `lint:js`, and `test` script. These can be run within the package itself and in bulk via the associated `pkg:*` commands from with the root directory of the repository.


## Publishing Releases

Releases are published using the `npm run lerna publish` command from the repository root on the `trunk` branch.

_Note: Packages which are included with the LifterLMS core should *always* be released alongside LifterLMS core releases._

To publish a release:

+ Run `npm run lerna changed` to see which packages have changes to be published.
+ Ensure the `lint:js` and `test` scripts pass.
+ Ensure changelogs have been updated to the appropriate version and the "Unreleased" header has been removed.
+ Run `npm run dev update-version -- -F {version}` to update `[version]` placeholders to the appropriate version.
+ Commit and push changes.
+ Run `npm run lerna publish` and follow the prompts.

