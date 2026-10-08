# wp llms lesson create

Creates a new lesson.

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

[\--parent_id=&lt;parent_id&gt;]
: WordPress post ID of the parent item. Must be a Section ID. 0 indicates an "orphaned" lesson which can be edited and viewed by instructors and admins but cannot be read by students.

\--order=&lt;order&gt;
: Order of the lesson within its immediate parent.
\---
default: 1
\---

[\--prerequisite=&lt;prerequisite&gt;]
: Lesson ID of the prerequisite lesson.

[\--points=&lt;points&gt;]
: Determines the weight of the lesson when grading the course.
\---
default: 1
\---

[\--audio_embed=&lt;audio_embed&gt;]
: URL to an oEmbed enable audio URL.

[\--video_embed=&lt;video_embed&gt;]
: URL to an oEmbed enable video URL.

[\--drip_date=&lt;drip_date&gt;]
: The date and time when the lesson becomes available. Applicable only when drip_method is date. Format: Y-m-d H:i:s.

[\--drip_days=&lt;drip_days&gt;]
: Number of days to wait before allowing access to the lesson. Applicable only when drip_method is enrollment, start, or prerequisite.
\---
default: 1
\---

[\--drip_method=&lt;drip_method&gt;]
: Determine the method with which to make the lesson content available.
					&lt;ul&gt;
						&lt;li&gt;none: Drip is disabled; the lesson is immediately available.&lt;/li&gt;
						&lt;li&gt;date: Lesson is made available at a specific date and time.&lt;/li&gt;
						&lt;li&gt;enrollment: Lesson is made available a specific number of days after enrollment into the course.&lt;/li&gt;
						&lt;li&gt;start: Lesson is made available a specific number of days after the course's start date. Only available on courses with a access_opens_date.&lt;/li&gt;
						&lt;li&gt;prerequisite: Lesson is made available a specific number of days after the prerequisite lesson is completed.&lt;/li&gt;
					&lt;/ul&gt;
\---
default: none
options:
  - none
  - date
  - enrollment
  - start
  - prerequisite
\---

[\--public=&lt;public&gt;]
: Denotes a lesson that's publicly accessible regardless of course enrollment.
\---
default: false
\---

[\--quiz=&lt;quiz&gt;]
: Associate a quiz with this lesson.

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
