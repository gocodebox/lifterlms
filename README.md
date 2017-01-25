LifterLMS [![Build Status](https://travis-ci.org/gocodebox/lifterlms.svg?branch=master)](https://travis-ci.org/gocodebox/lifterlms)
==========

[![Coverage Status](https://coveralls.io/repos/github/gocodebox/lifterlms/badge.svg)](https://coveralls.io/github/gocodebox/lifterlms)

[LifterLMS](https://lifterlms.com), the #1 WordPress LMS solution, makes it easy to create, sell, and protect engaging online courses.


### [Changelog](./CHANGELOG.md)


### Documentation
+ [https://lifterlms.com/docs/](https://lifterlms.com/docs/)
+ [https://lifterlms.readme.io](https://lifterlms.readme.io)


### Support

This is a developer's portal for the LifterLMS team and any members of the community who wish to contribute to LifterLMS.

This is _not_ a support form. If you require support please visit the [forums](https://wordpress.org/support/plugin/lifterlms) or become a [LifterLMS Pro Member](https://lifterlms.com/product/lifterlms-pro) and submit a [support ticket](https://lifterlms.com/my-account/my-tickets).


### Reporting a Bug

Bugs can be reported at [https://github.com/gocodebox/lifterlms/issues/new](https://github.com/gocodebox/lifterlms/issues/new).

Before reporting a bug, [search existing issues](https://github.com/gocodebox/lifterlms/issues) and ensure you're not creating a duplicate. If the issue already exists you can add your information to the existing report.

Also check our [known issues and conflicts](https://lifterlms.com/doc-category/lifterlms/known-conflicts/) for possible resolutions.


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


### Contributing

+ Fork the repository on GitHub (make sure to use the develop branch, not master).
+ Make the changes to your forked repository.
+ Ensure you stick to the WordPress Coding Standards and have properly documented any new functions, actions and filters following the documentation standards.
+ When committing, reference your issue (if present) and include a note about the fix.
+ Run PHPCS and ensure the output has no errors. We **will** reject pull requests if they fail codesniffing.
+ Push the changes to your fork
+ Submit a pull request to the 'develop' branch of the LifterLMS repo.
+ At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary. We're newly open source and supporting users and customers and our own internal pull requests and releases will take priority over pull requests from the community. Please be patient!
