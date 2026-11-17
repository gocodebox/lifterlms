# wp llms addon deactivate

Deactivate one or more plugin add-ons.

### OPTIONS

[&lt;slug&gt;...]
: The slug of one or more add-on to deactivate.

[\--uninstall]
: Uninstall the add-ons after deactivation.

[\--all]
: If set, all of the plugin add-ons installed on the site will be activated.

### EXAMPLES

    # Deactivate the LifterLMS Groups add-on.
    $ wp llms addon deactivate lifterlms-groups

    # Deactivate an add-on without using the `lifterlms-` prefix.
    $ wp llms addon deactivate advanced-videos

    # Deactivate multiple LifterLMS add-ons.
    $ wp llms addon deactivate lifterlms-groups lifterlms-assignments lifterlms-pdfs

    # Deactivate all installed LifterLMS add-ons.
    $ wp llms addon deactivate --all

    # Deactivate and uninstall the LifterLMS Groups add-on.
    $ wp llms addon deactivate lifterlms-groups --uninstall

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
