LifterLMS [![Build Status](https://travis-ci.com/gocodebox/lifterlms.svg?token=cynuTFxuKtxvAs4e2hNZ&branch=master)](https://travis-ci.com/gocodebox/lifterlms)
==========

LifterLMS, the #1 WordPress LMS solution, makes it easy to create, sell, and protect engaging online courses.

### [Changelog](./CHANGELOG.md)


### [Documentation](https://lifterlms.readme.io)


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

Do not use phpcs directly from the command line, use the gulp task.

+ `gulp phpcs` to run on all php files
+ `gulp phpcs --file path/to/file.php` to run on a specific file
+ `gulp phpcs --file valid/glob/*.php` pass a valid glob to run on a group of files
+ `gulp phpcs --warning 0` to ignore PHPCS warnings, or any valid phpcs warning severity level (1-8)


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
