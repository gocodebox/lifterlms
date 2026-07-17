# wp llms access-plan update

Updates an existing access-plan.

### OPTIONS

&lt;id&gt;
: The id for the resource.

[\--id=&lt;id&gt;]
: Unique identifier for the resource.

[\--date_created=&lt;date_created&gt;]
: Creation date. Format: Y-m-d H:i:s

[\--date_created_gmt=&lt;date_created_gmt&gt;]
: Creation date (in GMT). Format: Y-m-d H:i:s

[\--menu_order=&lt;menu_order&gt;]
: Creation date (in GMT). Format: Y-m-d H:i:s

[\--title=&lt;title&gt;]
: Post title.

[\--content=&lt;content&gt;]
: The HTML content of the post.

[\--price=&lt;price&gt;]
: Access plan price.

[\--access_expiration=&lt;access_expiration&gt;]
: Access expiration type. `lifetime` provides access until cancelled or until a recurring payment fails. `limited-period` provides access for a limited period as specified by `access_length` and `access_period` `limited-date` provides access until the date specified by access_expires_date`.
\---
options:
  - lifetime
  - limited-period
  - limited-date
\---

[\--access_expires=&lt;access_expires&gt;]
: Date when access expires. Only applicable when `access_expiration` is `limited-date`. `Format: Y-m-d H:i:s`.

[\--access_length=&lt;access_length&gt;]
: Determine the length of access from time of purchase. Only applicable when `access_expiration` is `limited-period`.

[\--access_period=&lt;access_period&gt;]
: Determine the length of access from time of purchase. Only applicable when `access_expiration` is `limited-period`
\---
options:
  - year
  - month
  - week
  - day
\---

[\--availability_restrictions=&lt;availability_restrictions&gt;]
: Restrict usage of this access plan to students enrolled in at least one of the specified memberships.

[\--enroll_text=&lt;enroll_text&gt;]
: Text of the "Purchase" button

[\--frequency=&lt;frequency&gt;]
: Billing frequency [0-6]. `0` denotes a one-time payment. `&gt;= 1` denotes a recurring plan.

[\--length=&lt;length&gt;]
: For recurring plans only. Determines the number of intervals a plan should run for. `0` denotes the plan should run until cancelled.

[\--period=&lt;period&gt;]
: For recurring plans only. Determines the interval of recurring payments.
\---
options:
  - year
  - month
  - week
  - day
\---

[\--post_id=&lt;post_id&gt;]
: Determines the course or membership which can be accessed through the plan.

[\--redirect_forced=&lt;redirect_forced&gt;]
: Use this plans's redirect settings when purchasing a Membership this plan is restricted to. Applicable only when `availability_restrictions` exist for the plan

[\--redirect_page=&lt;redirect_page&gt;]
: WordPress page ID to use for checkout success redirection. Applicable only when `redirect_type` is page.

[\--redirect_type=&lt;redirect_type&gt;]
: Determines the redirection behavior of the user's browser upon successful checkout or registration through the plan. `self`: Redirect to the permalink of the specified `post_id`. `page`: Redirect to the permalink of the WordPress page specified by `redirect_page_id`. `url`: Redirect to the URL specified by `redirect_url`.
\---
options:
  - self
  - page
  - url
\---

[\--redirect_url=&lt;redirect_url&gt;]
: URL to use for checkout success redirection. Applicable only when `redirect_type` is `url`.

[\--sale_date_end=&lt;sale_date_end&gt;]
: Used to automatically end a scheduled sale. If empty, the plan remains on sale indefinitely. Only applies when `sale_enabled` is `true`. Format: `Y-m-d H:i:s`.

[\--sale_date_start=&lt;sale_date_start&gt;]
: Used to automatically start a scheduled sale. If empty, the plan is on sale immediately. Only applies when `sale_enabled` is `true`. Format: `Y-m-d H:i:s`.

[\--sale_enabled=&lt;sale_enabled&gt;]
: Mark the plan as "On Sale" allowing for temporary price adjustments.

[\--sale_price=&lt;sale_price&gt;]
: Sale price. Only applies when `sale_enabled` is `true`.

[\--sku=&lt;sku&gt;]
: External identifier

[\--trial_enabled=&lt;trial_enabled&gt;]
: Enable a trial period for a recurring access plan.

[\--trial_length=&lt;trial_length&gt;]
: Determines the length of trial access. Only applies when `trial_enabled` is `true`.

[\--trial_period=&lt;trial_period&gt;]
: Determines the length of trial access. Only applies when `trial_enabled` is `true`.
\---
options:
  - year
  - month
  - week
  - day
\---

[\--trial_price=&lt;trial_price&gt;]
: Determines the price of the trial period. Only applies when `trial_enabled` is `true`.

[\--visibility=&lt;visibility&gt;]
: Access plan visibility.
\---
options:
  - visible
  - hidden
  - featured
\---

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
