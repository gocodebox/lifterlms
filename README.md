LifterLMS
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

Coming soon! Pull requests welcome. Bear with us as we get up to speed on how to open source most effectively.
