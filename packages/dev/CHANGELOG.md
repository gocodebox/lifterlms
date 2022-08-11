@lifterlms/dev CHANGELOG
========================

v0.1.0 - 2022-08-11
-------------------

+ Updated: [**BREAKING**] During prerelease builds the `readme` command will now exit with code `0` and output a warning message instead of previous behavior: exit code `1` with an error message. 
+ Added: The `readme` command now merges additional merge codes as derived from the `parseMainFileMetadata()` utility function.
+ Added: A New command `meta` has been added.
+ Added: `docgen` will now include `beforeHelp` and `afterHelp` text added via `Command.addHelpText()`.


v0.0.5 - 2022-05-23
-------------------

+ Added: `update-version` default replacements will now additionally replace the `[version]` placeholder in the following functions: `_deprecated_argument`, `_deprecated_constructor`, `_deprecated_hook`, `_doing_it_wrong`, `apply_filters_deprecated`, and `do_action_deprecated`.
+ Added: `update-version` command RegEx lists now accept an optional 3rd item used to specify the `RegExp` flags argument. If not supplied the flags list defaults to `g`.


v0.0.4 - 2022-02-15
-------------------

+ Added: New utility methods for generating links to the project's GitHub repository.
+ Fixed: Incorrect issue link URL generated during `changelog write` command.


v0.0.3 - 2021-12-23
-------------------

+ Bugfix: [**Breaking**] The short option `-t` for the `--title` option for the `changelog add` command has been changed to `-T`.


v0.0.2 - 2021-11-10
-------------------

+ Added flag `--links` to `changelog write` command in order to allow default flag configuration via the `.yml` config file. The flag is enabled by default for public repos and disabled for private ones.
+ Fixed an OSX issue encountered in the `changelog write` command resulting from the use of `xargs -d`.
+ Fixed an issue causing `changelog version next` to fail when passing the `--preid` flag.
+ Don't provide a default value to the `update-version` command option `--preid`.


v0.0.1 - 2021-11-05
-------------------

+ Initial release.
