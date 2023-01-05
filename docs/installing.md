Installing for Development
==========================

## Requirements

In order to build and develop LifterLMS locally, you'll need the following:

+ PHP
+ MySQL / MariaDB
+ [Composer](https://getcomposer.org/download/)
+ [Node.js](https://nodejs.org/en/download/)
+ [npm](https://docs.npmjs.com/downloading-and-installing-node-js-and-npm)


## Building LifterLMS

### 1. Clone source from GitHub

```sh
$ git clone https://github.com/gocodebox/lifterlms
$ cd lifterlms
```

If you're planning to contribute code, you should fork this repository and clone your fork instead and switch to the dev branch before continuing the install.

```sh
$ git checkout dev
```

### 2. Install composer dependencies:

```sh
$ composer install
```

### 3. Install npm dependencies:

```sh
$ npm install --global gulp
$ npm install
```

### 4. Build static assets

```sh
$ npm run build
```

The `lifterlms` directory is now an installable plugin that can be moved into your local server's `wp-content/plugins` directory.


## Running PHPCS

When contributing you should ensure your contributions follow our [coding](./coding-standards.md) and [documentation](./documentation-standards.md) standards.

To check for errors and warnings in your code, run PHPCS:

```sh
$ composer run check-cs
```

To check for errors only:

```sh
$ composer run check-cs-errors
```

These reports may include issues that can be automatically fixed using PHPCBF:

```sh
$ composer run fix-cs
```

## Running Test Suites

New code should also strive to be covered by automated tests.

LifterLMS has unit and integration tests via phpunit and End-to-End tests via Jest and Puppeteer.

For guides on running and contributing tests, see the relevant guides:

+ [phpunit](../tests/phpunit/README.md)
+ [e2e](../tests/e2e/README.md)
