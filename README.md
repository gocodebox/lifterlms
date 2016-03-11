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
+ Ensure you stick to the WordPress Coding Standards and have properly documented any new functions, actions and filters following the documentation standards (we're working on automated testing, code sniffing, etc... our codebase is fragmented and doesn't fully adhere to our own standards but we're working on it. Don't create more work for us and don't make us reject you for lack of inline documentation!)
+ When committing, reference your issue (if present) and include a note about the fix.
+ Push the changes to your fork and submit a pull request to the 'develop' branch of the LifterLMS repo.
+ At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary. We're newly open source and supporting users and customers and our own internal pull requests and releases will take priority over pull requests from the community. Please be patient!
