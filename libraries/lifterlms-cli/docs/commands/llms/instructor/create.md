# wp llms instructor create

Creates a new instructor.

### OPTIONS

[\--username=&lt;username&gt;]
: Login name for the user.

[\--name=&lt;name&gt;]
: Display name for the user.

[\--first_name=&lt;first_name&gt;]
: First name for the user.

[\--last_name=&lt;last_name&gt;]
: Last name for the user.

\--email=&lt;email&gt;
: The email address for the user.

[\--url=&lt;url&gt;]
: URL of the user.

[\--description=&lt;description&gt;]
: Description of the user.

[\--nickname=&lt;nickname&gt;]
: The nickname for the user.

[\--roles=&lt;roles&gt;]
: Roles assigned to the user.
\---
default:
  - instructor
\---

[\--password=&lt;password&gt;]
: Password for the user (never included).

[\--billing_address_1=&lt;billing_address_1&gt;]
: User address line 1.

[\--billing_address_2=&lt;billing_address_2&gt;]
: User address line 2.

[\--billing_city=&lt;billing_city&gt;]
: User address city name.

[\--billing_state=&lt;billing_state&gt;]
: User address ISO code for the state, province, or district.

[\--billing_postcode=&lt;billing_postcode&gt;]
: User address postal code.

[\--billing_country=&lt;billing_country&gt;]
: User address ISO code for the country.

[\--porcelain]
: Output just the id when the operation is successful.

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
