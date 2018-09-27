LifterLMS Coding Standards
==========================

The purpose of the LifterLMS Coding Standards is to create a baseline for collaboration and review within the open source LifterLMS codebase, project, and community.

The WordPress community has developed coding standards and documented them in the [WordPress codex](https://make.wordpress.org/core/handbook/best-practices/coding-standards/). Wherever possible, the LifterLMS Coding Standards aim to obey these coding standards.

## Naming Conventions

### camelCase should not be used.

LifterLMS avoids `camelCase` for class names, class methods, functions, and variables. Words should instead be separated by underscores.

### Class Names

Class names should use capitalized words separated by underscores.
LifterLMS core class names should be prefixed with `LLMS_`.


```php
class LLMS_Student extends LLMS_Abstract_User_Data { [...] }
class LLMS_Data { [...] }
```

LifterLMS add-on class names should be prefixed with with `LLMS_` as well as an additional add-on prefix.

```php
class LLMS_AQ_Question_Types { [...] }
class LLMS_SL_Story extends LLMS_Abstract_Database_Store { [...] }
```

### Constants

Constants should be in all upper-case with underscores separating words.
LifterLMS core constants should be prefixed with `LLMS_`.

```php
define( 'LLMS_PLUGIN_FILE', __FILE__ );
```

LifterLMS add-on class names should be prefixed with with `LLMS_` as well as an additional add-on prefix.

```php
define( 'LLMS_FORMIDABLE_FORMS_PLUGIN_FILE', __FILE__ );
```

### File names

Files should be named descriptively using lower case letters. Hyphens should be used to separate words.

```
my-plugin-file.php
```

Class file names should be based on the class name with `class-` prepended and the underscores in the class name replaced with hyphens, for example `LLMS_Data` becomes:

```
class-llms-data.php
```

Files containng model classes should prepend `model-` instead of `class-`. For example the `LLMS_Student` model class becomes:

```
model-llms-student.php
```

### Functions & Variables

Lowercase letters should be used for function names and variables. Separate words with underscores.
LifterLMS core functions should be prepended with the prefix `llms_`.

```php
llms_current_time( $type, $gmt = 0 ) { [...] }
```

LifterLMS add-on function names should be prefixed with with `llms_` as well as an additional add-on prefix.

```php
llms_ck_consent_form_field() { [...] }
```

### Hooks: Actions & Filters

Lowercase letters should be used for hook names. Separate words with underscores.
LifterLMS core hooks should be prepended with the prefix `llms_`.

```php
do_action( 'llms_user_enrolled_in_course', [...] );
apply_filters( 'llms_get_enrollment_status', [...] );
```

LifterLMS add-on hook names should be prefixed with with `llms_` as well as an additional add-on prefix.

```php
do_action( 'llms_pa_post_created_from_automation', [...] );
apply_filters( 'llms_sl_story_can_user_manage', [...] );
```

When actions are set to run before and after items (templates, as an example) it is acceptable to use additional prefixes `before_` and `after_` prior to the `llms_` prefix.

There are a number of legacy hooks which use the prefix `lifterlms_` instead of `llms_`. These are retained for backwards compatibility but should not be used as an example of an acceptable naming convention for new code.

