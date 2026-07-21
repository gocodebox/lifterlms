# wp llms course create

Creates a new course.

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

[\--catalog_visibility=&lt;catalog_visibility&gt;]
: Visibility of the course in catalogs and search results.
\---
default: catalog_search
options:
  - catalog_search
  - catalog
  - search
  - hidden
\---

[\--categories=&lt;categories&gt;]
: List of course categories.

[\--tags=&lt;tags&gt;]
: List of course tags.

[\--difficulties=&lt;difficulties&gt;]
: List of course difficulties.

[\--tracks=&lt;tracks&gt;]
: List of course tracks.

[\--instructors=&lt;instructors&gt;]
: List of course instructors. Defaults to current user when creating a new post.

[\--audio_embed=&lt;audio_embed&gt;]
: URL to an oEmbed enable audio URL.

[\--video_embed=&lt;video_embed&gt;]
: URL to an oEmbed enable video URL.

[\--capacity_enabled=&lt;capacity_enabled&gt;]
: Determines if an enrollment capacity limit is enabled.
\---
default: false
\---

[\--capacity_limit=&lt;capacity_limit&gt;]
: Number of students who can be enrolled in the course before enrollment closes.

[\--capacity_message=&lt;capacity_message&gt;]
: Message displayed when enrollment capacity has been reached.

[\--prerequisite=&lt;prerequisite&gt;]
: Course ID of the prerequisite course.

[\--prerequisite_track=&lt;prerequisite_track&gt;]
: Term ID of a prerequisite track.

[\--length=&lt;length&gt;]
: User defined course length.

[\--restricted_message=&lt;restricted_message&gt;]
: Message displayed when non-enrolled visitors try to access restricted course content (lessons, quizzes, etc..) directly.
\---
default: &gt;
  You must enroll in this course to access
  course content.
\---

[\--access_closes_date=&lt;access_closes_date&gt;]
: Date when the course closes. After this date enrolled students may no longer view and interact with the restricted course content.
					If blank the course is open indefinitely after the the access_opens_date has passed.
					Does not affect course enrollment, see enrollment_opens_date to control the course enrollment close date.
					Format: Y-m-d H:i:s.

[\--access_closes_message=&lt;access_closes_message&gt;]
: Message displayed to enrolled students when the course is accessed after the access_closes_date has passed.
\---
default: 'This course closed on [lifterlms_course_info id="{{course_id}}" key="end_date"].'
\---

[\--access_opens_date=&lt;access_opens_date&gt;]
: Date when the course opens, allowing enrolled students to begin to view and interact with the restricted course content.
					If blank the course is open until after the access_closes_date has passed.
					Does not affect course enrollment, see enrollment_opens_date to control the course enrollment start date.
					Format: Y-m-d H:i:s.

[\--access_opens_message=&lt;access_opens_message&gt;]
: Message displayed to enrolled students when the course is accessed before the access_opens_date has passed.
\---
default: 'This course opens on [lifterlms_course_info id="{{course_id}}" key="start_date"].'
\---

[\--enrollment_closes_date=&lt;enrollment_closes_date&gt;]
: Date when the course enrollment closes.
					If blank course enrollment is open indefinitely after the the enrollment_opens_date has passed.
					Does not affect course content access, see access_opens_date to control course access close date.
					Format: Y-m-d H:i:s.

[\--enrollment_closes_message=&lt;enrollment_closes_message&gt;]
: Message displayed to visitors when attempting to enroll into a course after the enrollment_closes_date has passed.
\---
default: 'Enrollment in this course closed on [lifterlms_course_info id="{{course_id}}" key="enrollment_end_date"].'
\---

[\--enrollment_opens_date=&lt;enrollment_opens_date&gt;]
: Date when the course enrollment opens.
					If blank course enrollment is open until after the enrollment_closes_date has passed.
					Does not affect course content access, see access_opens_date to control course access start date.
					Format: Y-m-d H:i:s.

[\--enrollment_opens_message=&lt;enrollment_opens_message&gt;]
: Message displayed to visitors when attempting to enroll into a course before the enrollment_opens_date has passed.
\---
default: 'Enrollment in this course opens on [lifterlms_course_info id="{{course_id}}" key="enrollment_start_date"].'
\---

[\--sales_page_page_id=&lt;sales_page_page_id&gt;]
: The WordPress page ID of the sales page. Required when sales_page_type equals page. Only returned when the sales_page_type equals page.

[\--sales_page_type=&lt;sales_page_type&gt;]
: Determines the type of sales page content to display.&lt;br&gt; - &lt;code&gt;none&lt;/code&gt; displays the course content.&lt;br&gt; - &lt;code&gt;content&lt;/code&gt; displays alternate content from the &lt;code&gt;excerpt&lt;/code&gt; property.&lt;br&gt; - &lt;code&gt;page&lt;/code&gt; redirects to the WordPress page defined in &lt;code&gt;content_page_id&lt;/code&gt;.&lt;br&gt; - &lt;code&gt;url&lt;/code&gt; redirects to the URL defined in &lt;code&gt;content_page_url&lt;/code&gt;
\---
default: none
options:
  - none
  - content
  - page
  - url
\---

[\--sales_page_url=&lt;sales_page_url&gt;]
: The URL of the sales page content. Required when &lt;code&gt;content_type&lt;/code&gt; equals &lt;code&gt;url&lt;/code&gt;. Only returned when the &lt;code&gt;content_type&lt;/code&gt; equals &lt;code&gt;url&lt;/code&gt;.

[\--video_tile=&lt;video_tile&gt;]
: When true the video_embed will be used on the course tiles (on the catalog, for example) instead of the featured image.
\---
default: false
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
