LifterLMS CLI Changelog
=======================

v0.0.4 - 2024-07-09
-------------------

##### Security Fixes

+ Adds additional security checks and escaping.


v0.0.3 - 2021-11-03
-------------------

+ Improved help documentation for several commands.
+ Added a warning to the root command's help documentation denoting that the LLMS-CLI is in open public beta and its functionality is subject to change.


v0.0.2 - 2021-10-15
-------------------

+ Use a strict comparison when checking response status using the `license` command.
+ Remove `--db` option from the `version` command. This will be implemented in a separate command.
+ Fixed an unmerged placeholder in warning message when add-on is not installed when using the `activate`.
+ Updated success message when using `channel set`.
+ Completion messages use says "deactivate(d)" in favor of "activate(d)" in the `addon deactivate` command.


v0.0.1 - 2021-07-27
-------------------

+ Initial public release
