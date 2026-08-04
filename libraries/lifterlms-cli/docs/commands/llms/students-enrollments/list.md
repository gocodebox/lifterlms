# wp llms students-enrollments list

Gets a list of  students-enrollmentss.

### OPTIONS

[\--context=&lt;context&gt;]
: Scope under which the request is made; determines fields present in response.
\---
default: view
options:
  - view
  - edit
\---

[\--page=&lt;page&gt;]
: Current page of the collection.
\---
default: 1
\---

[\--per_page=&lt;per_page&gt;]
: Maximum number of items to be returned in result set.
\---
default: 10
\---

[\--order=&lt;order&gt;]
: Order sort attribute ascending or descending.
\---
default: asc
options:
  - asc
  - desc
\---

[\--orderby=&lt;orderby&gt;]
: Sort collection by object attribute.
\---
default: date_created
options:
  - date_created
  - date_updated
\---

[\--status=&lt;status&gt;]
: Filter results to records matching the specified status.
\---
options:
  - cancelled
  - enrolled
  - expired
\---

[\--post=&lt;post&gt;]
: Limit results to a specific course or membership or a list of courses and/or memberships. Accepts a single post id or a comma separated list of post ids.

[\--fields=&lt;fields&gt;]
: Limit response to specific fields. Defaults to all fields.

[\--field=&lt;field&gt;]
: Get the value of an individual field.

[\--format=&lt;format&gt;]
: Render response in a particular format.
\---
default: table
options:
  - table
  - json
  - csv
  - ids
  - yaml
  - count
  - headers
  - body
  - envelope
\---

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
