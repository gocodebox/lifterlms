[![LifterLMS](https://3xwbw71rswfz42rmgp5qgl85-wpengine.netdna-ssl.com/wp-content/uploads/2015/03/logo.png "LifterLMS")](https://lifterlms.com)

[![WordPress plugin](https://img.shields.io/wordpress/plugin/v/lifterlms.svg)]()
[![WordPress](https://img.shields.io/wordpress/v/lifterlms.svg)]()
[![WordPress rating](https://img.shields.io/wordpress/plugin/r/lifterlms.svg)]()
[![WordPress](https://img.shields.io/wordpress/plugin/dt/lifterlms.svg)]()
[![Build Status](https://travis-ci.org/gocodebox/lifterlms.svg?branch=master)](https://travis-ci.org/gocodebox/lifterlms)
[![Code Climate](https://codeclimate.com/github/gocodebox/lifterlms/badges/gpa.svg)](https://codeclimate.com/github/gocodebox/lifterlms)
[![Test Coverage](https://codeclimate.com/github/gocodebox/lifterlms/badges/coverage.svg)](https://codeclimate.com/github/gocodebox/lifterlms/coverage)

[LifterLMS](https://lifterlms.com), the #1 WordPress LMS solution, makes it easy to create, sell, and protect engaging online courses.


### [Changelog](./CHANGELOG.md)


### Documentation
+ [https://lifterlms.com/docs/](https://lifterlms.com/docs/)


### Getting Help and Support Support

GitHub is for bug reports and contributions only! If you have a support question or a request for a customization this is not the right place to post it. Please refer to [LifterLMS Support](https://lifterlms.com/my-account/my-tickets) or the [community forums](https://wordpress.org/support/plugin/lifterlms). If you're looking for help customizing LifterLMS, please consider hiring a [LifterLMS Expert](https://lifterlms.com/docs/do-you-have-any-recommended-developers-who-can-modifycustomize-lifterlms/).


### Reporting a Bug

Bugs can be reported at [https://github.com/gocodebox/lifterlms/issues/new](https://github.com/gocodebox/lifterlms/issues/new).

Before reporting a bug, [search existing issues](https://github.com/gocodebox/lifterlms/issues) and ensure you're not creating a duplicate. If the issue already exists you can add your information to the existing report.

Also check our [known issues and conflicts](https://lifterlms.com/doc-category/lifterlms/known-conflicts/) for possible resolutions.

### Contributing [![contributions welcome](https://img.shields.io/badge/contributions-welcome-brightgreen.svg?style=flat)](.github/CONTRIBUTING.md)

Interested in contributing to LifterLMS? We'd love to have your contributions. Read our contributor's guidelines [here](.github/CONTRIBUTING.md).

### Installing for Production Usage

If you clone or download this repo directly it will not run as a plugin inside WordPress! Installable production releases are available in on the [Releases tab](https://github.com/gocodebox/lifterlms/releases). You can get the latest stable release from [WordPress.org](https://downloads.wordpress.org/plugin/lifterlms.zip)

### Installing for Development

1. Composer
  + `curl -sS https://getcomposer.org/installer | php`
  + `php composer.phar install`

2. Node
  + Install node
  + Install npm
  + `npm install --global gulp`
  + `npm install`


### Coding Standards

For standards we're working off a modified version of the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/).

We're utilizing (a currently slightly modified version of) the [WordPress Coding Standards Core Ruleset](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) for PHPCS (php codesniffing).

Our javascript and SCSS are a mess. We're tackling that next.


### Running phpcs

Use the shorthand composer script to run phpcs against all PHP files.

+ `composer run-script phpcs`

Alternatively access the executable:

+ `./vendor/bin/phpcs path/to/file.php`

To see errors only (no warnings):

+ `./vendor/bin/phpcs -n path/to/file.php`

To see all options:

+ `./vendor/bin/phpcs -h`


### Running phpcbf

+ `./vendor/bin/phpcbf` to run on all php files
+ `./vendor/bin/phpcbf path/to/file.php` to run on a specific file

### Partners

[BrowserStack](https://www.browserstack.com/) helps us ensure LifterLMS looks great and works on every imaginable browser and device.

[![BrowserStack](https://raw.githubusercontent.com/gocodebox/lifterlms/master/.github/sponsors/browserstack-logo.png "BrowserStack")](https://www.browserstack.com/)



[StagingPilot](https://stagingpilot.com/) helps us automate acceptance testing to ensure LifterLMS remains compatible with popular WordPress themes and plugins.

[![StagingPilot](https://raw.githubusercontent.com/gocodebox/lifterlms/master/.github/sponsors/stagingpilot-logo.png "StagingPilot")](https://stagingpilot.com/)

