# wp llms membership create

Creates a new membership.

### OPTIONS

[\--date_created=&lt;date_created&gt;]
: Creation date. Format: Y-m-d H:i:s

[\--date_created_gmt=&lt;date_created_gmt&gt;]
: Creation date (in GMT). Format: Y-m-d H:i:s

[\--menu_order=&lt;menu_order&gt;]
: Creation date (in GMT). Format: Y-m-d H:i:s
\---
default: 0
\---

\--title=&lt;title&gt;
: Post title.

\--content=&lt;content&gt;
: The HTML content of the post.

[\--excerpt=&lt;excerpt&gt;]
: The HTML excerpt of the post.

[\--slug=&lt;slug&gt;]
: Post URL slug.

[\--status=&lt;status&gt;]
: The publication status of the post.
\---
default: publish
options:
  - publish
  - future
  - draft
  - pending
  - private
\---

[\--password=&lt;password&gt;]
: Password used to protect access to the content.

[\--featured_media=&lt;featured_media&gt;]
: Featured image ID.

[\--comment_status=&lt;comment_status&gt;]
: Post comment status. Default comment status dependent upon general WordPress post discussion settings.
\---
default: open
options:
  - open
  - closed
\---

[\--ping_status=&lt;ping_status&gt;]
: Post ping status. Default ping status dependent upon general WordPress post discussion settings.
\---
default: open
options:
  - open
  - closed
\---

[\--auto_enroll=&lt;auto_enroll&gt;]
: List of courses to automatically enroll students into when they're enrolled into the membership.
\---
default: [ ]
\---

[\--catalog_visibility=&lt;catalog_visibility&gt;]
: Visibility of the membership in catalogs and search results.
\---
default: catalog_search
options:
  - catalog_search
  - catalog
  - search
  - hidden
\---

[\--categories=&lt;categories&gt;]
: List of membership categories.

[\--instructors=&lt;instructors&gt;]
: List of post instructors. Defaults to current user when creating a new post.

[\--restriction_action=&lt;restriction_action&gt;]
: Determines the action to take when content restricted by the membership is accessed by a non-member. - `none`: Remain on page and display the message `restriction_message`. - `membership`: Redirect to the membership's permalink. - `page`: Redirect to the permalink of the page identified by `restriction_page_id`. - `custom`: Redirect to the URL identified by `restriction_url`.
\---
default: none
options:
  - none
  - membership
  - page
  - custom
\---

[\--restriction_message=&lt;restriction_message&gt;]
: Message to display to non-members after a `restriction_action` redirect. When `restriction_action` is `none` replaces the page content with this message.
\---
default: 'You must belong to the [lifterlms_membership_link id="{{membership_id}}"] membership to access this content.'
\---

[\--restriction_page_id=&lt;restriction_page_id&gt;]
: WordPress page ID used for redirecting non-members when `restriction_action` is `page`.

[\--restriction_url=&lt;restriction_url&gt;]
: URL used for redirecting non-members when `restriction_action` is `custom`.

[\--sales_page_page_id=&lt;sales_page_page_id&gt;]
: The WordPress page ID of the sales page. Required when `sales_page_type` equals `page`. Only returned when the `sales_page_type` equals `page`.

[\--sales_page_type=&lt;sales_page_type&gt;]
: Defines alternate content displayed to visitors and non-enrolled students when accessing the post. - `none` displays the post content. - `content` displays alternate content from the `excerpt` property. - `page` redirects to the WordPress page defined in `content_page_id`. - `url` redirects to the URL defined in `content_page_url`.
\---
default: none
options:
  - none
  - content
  - page
  - url
\---

[\--sales_page_url=&lt;sales_page_url&gt;]
: The URL of the sales page content. Required when `sales_page_type` equals `url`. Only returned when the `sales_page_type` equals `url`.

[\--tags=&lt;tags&gt;]
: List of membership tags.

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
