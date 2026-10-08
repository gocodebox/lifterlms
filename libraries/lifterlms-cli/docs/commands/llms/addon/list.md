# wp llms addon list

Gets a list of add-ons.

Displays a list of add-ons with their activation status, license status, current version, update availability, etc...

### OPTIONS

[\--&lt;field&gt;=&lt;value&gt;]
: Filter results based on the value of a field.

[\--field=&lt;field&gt;]
: Prints the value of a single field for each add-on.

[\--fields=&lt;fields&gt;]
: Limit the output to only the specified fields. Use "all" to display all available fields.

[\--format=&lt;format&gt;]
: Render output in a particular format.
\---
default: table
options:
  - table
  - csv
  - count
  - json
  - yaml
\---

### AVAILABLE FIELDS

These fields will be displayed by default for each add-on:

* name
* status
* update
* version

These fields are optionally available:

* update_version
* license
* title
* channel
* type
* file

### EXAMPLES

    # List all add-ons.
    $ wp llms addon list

    # List all add-ons in JSON format.
    $ wp llms addon list --format=json

    # List all add-ons by name only.
    $ wp llms addon list --field=name

    # List all add-ons with all available fields.
    $ wp llms addon list --fields=all

    # List all add-ons with a custom fields list.
    $ wp llms addon list --fields=title,status,version

    # List currently activated add-ons.
    $ wp llms addon list --status=active

    # List all theme add-ons.
    $ wp llms addon list --type=theme

    # List all add-ons with available updates.
    $ wp llms addon list --update=available

    # List all add-ons licensed on the site.
    $ wp llms addon list --license=active

### GLOBAL PARAMETERS

These [global parameters](https://make.wordpress.org/cli/handbook/config/) have the same behavior across all commands and affect how WP-CLI interacts with WordPress.

| **Argument**    | **Description**              |
|:----------------|:-----------------------------|
| `--path=<path>` | Path to the WordPress files. |
| `--url=<url>` | Pretend request came from given URL. In multisite, this argument is how the target site is specified. |
| `--ssh=[<scheme>:][<user>@]<host\|container>[:<port>][<path>]` | Perform operation against a remote server over SSH (or a container using scheme of "docker", "docker-compose", "vagrant"). |
| `--http=<http>` | Perform operation against a remote WordPress installation over HTTP. |
| `--user=<id\|login\|email>` | Set the WordPress user. |
| `--skip-plugins[=<plugins>]` | Skip loading all plugins, or a comma-separated list of plugins. Note: mu-plugins are still loaded. |
| `--skip-themes[=<themes>]` | Skip loading all themes, or a comma-separated list of themes. |
| `--skip-packages` | Skip loading all installed packages. |
| `--require=<path>` | Load PHP file before running the command (may be used more than once). |
| `--exec=<php-code>` | Execute PHP code before running the command (may be used more than once). |
| `--[no-]color` | Whether to colorize the output. |
| `--debug[=<group>]` | Show all PHP errors and add verbosity to WP-CLI output. Built-in groups include: bootstrap, commandfactory, and help. |
| `--prompt[=<assoc>]` | Prompt the user to enter values for all command arguments, or a subset specified as comma-separated values. |
| `--quiet` | Suppress informational messages. |
