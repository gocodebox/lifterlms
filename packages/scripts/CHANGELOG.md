@lifterlms/scripts CHANGELOG
============================

v2.2.0 - 2022-01-31
-------------------

+ Update: `@wordpress/scripts` to [20.0.2](https://github.com/WordPress/gutenberg/blob/trunk/packages/scripts/CHANGELOG.md#2002-2022-01-31).
+ Update: `@jest/test-sequencer` to [27.4.6](https://github.com/facebook/jest/releases/tag/v27.4.6).
+ Update: The e2e bootstrap file will automatically attempt to intuit the WordPress core version being tested and store it in the `process.env.WP_VERSION`.


v2.1.0 - 2021-12-13
-------------------

+ Added webpack configuration option to customize the `cleanAfterEveryBuildPatterns` setting of the `CleanWebpackPlugin`.


v2.0.0 - 2021-11-05
-------------------

+ **[Breaking]** Raised the minimum required `@wordpress/scripts` version to 18.1.0.
+ **[Breaking]** Removes the failed test screenshot reporter in favor of the reporter included with `@wordpress/scripts`.
+ **[Breaking]** Failed test screenshots are now stored in the `tmp/artifacts` directory.
+ **[BREAKING]** Remove the default `DependencyExtractionWebpackPlugin` in favor of our custom loader from generated webpack configs.
+ Adds env var loading from `.llmsenv` with a fallback to `.llmsenv.dist`. The former file intended to be excluded from version control systems.
+ Adds a default `.eslintrc.js` configuration intended for use by LifterLMS and LifterLMS projects (via `wp-scripts lint-js`).


v2.0.0-beta.1 - 2021-09-10
--------------------------

+ **[Breaking]** Raised the minimum required `@wordpress/scripts` version to 17.1.0.
+ **[Breaking]** Removes the failed test screenshot reporter in favor of the reporter included with `@wordpress/scripts`.
+ **[Breaking]** Failed test screenshots are now stored in the `tmp/artifacts` directory.
+ Adds env var loading from `.llmsenv` with a fallback to `.llmsenv.dist`. The former file intended to be excluded from version control systems.
+ Adds a default `.eslintrc.js` configuration intended for use by LifterLMS and LifterLMS projects (via `wp-scripts lint-js`).


v1.3.3 - 2021-01-07
-------------------

+ Updated screenshot reporter function to include additional debugging information


v1.3.1 - 2020-08-11
-------------------

+ Don't use imports.


v1.3.0 - 2020-08-11
-------------------

+ Modify the `jest-puppeteer.config.js` to use defaults from `@wordpress/scripts`.


v1.2.4 - 2020-08-10
-------------------

+ Resolve script files for better portability.


v1.2.3 - 2020-08-10
-------------------

+ Add a configurable source file path option and set the default to `src/` instead of `assets/src` to the `webpack.config.js` generator.


v1.2.1 - 2020-07-21
-------------------

+ Update webpack config code for reduced complexity.


v1.2.0 - 2020-07-17
-------------------

+ Added webpack config "generator" method.
