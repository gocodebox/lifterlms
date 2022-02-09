LifterLMS Inline Documentation Standards
========================================

The LifterLMS documentation standard is heavily inspired by the [WordPress core's documentation standards][wp-core-docs]. We have made customizations to these standards in areas where it aids our core team's development and release workflows. By using the WordPress core documentation standard as a starting point any contributor already familiar with the WordPress core should be able to quickly add inline documentation to LifterLMS without the need to study our standards at length.

## What should be documented

The following elements should be documented using formatted documentation blocks (DocBlocks):

+ Functions
+ Classes
+ Class methods
+ Class members (including properties and constants)
+ Requires and includes
+ Hooks (actions and filters)
+ File headers

## DocBlock Formatting Guidelines

Inline documentation in the LifterLMS code base is automatically parsed and output to the code reference [developer.lifterlms.com][llms-dev]. Adhering to these guidelines is essential to ensure optimum readability via the code reference.


### Spacing

DocBlocks should directly precede the element (hook, function, method, class, etc...). There should not be any opening/closing tags, white space, or anything else between the DocBlock and the declarations. This will ensure the parser can correctly associate the DocBlock with it's element.


### Summary

A short piece of text, usually one line, providing the basic function of the associated element. A good summary concisely describes what the element does and should not attempt to describe why the element exists.

HTML may not be used in the summary. For example, if the function outputs an `<img>` tag, the summary should read ```Outputs an image tag.``` instead of ```Outputs an `<img>` tag.```.


### Description

An optional longer piece of text providing more details on the associated element’s function.

HTML may not be used in the summary but markdown can be used to format a complicated description.

**1. Lists**

Use a hyphen (`-`) to create an unordered list, with a blank line before and after.

```
 * Description which includes an unordered list:
 *
 * - This is item 1.
 * - This is item 2.
 * - This is item 3.
 *
 * The description continues on ...
```

Use numbers to create an ordered list, with a blank line before and after.

```
 * Description which includes an ordered list:
 *
 * 1. This is item 1.
 * 2. This is item 2.
 * 3. This is item 3.
 *
 * The description continues on ...
```

**2. Code Samples**

A code sample may be created by indenting every line of the code by 4 spaces, with a blank line before and after. Blank lines in code samples also need to be indented by four spaces. Note that examples added in this way will be output in `<pre>` tags and are not syntax-highlighted in the code reference.

```
  * Description including a code sample:
  *
  *    $status = array(
  *        'draft'   => __( 'Draft' ),
  *        'pending' => __( 'Pending Review' ),
  *        'private' => __( 'Private' ),
  *        'publish' => __( 'Published' )
  *    );
  *
  * The description continues on ...
```

**3. Links**

A link in the form of a URL, such as related GitHub issue or other documentation, should be added in the appropriate place in the DocBlock using the `@link` tag.

```
 * Description text.
 *
 * @link https://github.com/gocodebox/lifterlms/issues/1234567890
```

### Changelogs

Whenever any code is changed within an element, a `@since`, `@version`, or `@deprecated` tag should be added to the element to document the change(s) which have been made.

No HTML should be used in the descriptions for these tags, though limited Markdown can be used as necessary, such as for adding backticks around variables, e.g. `$variable`.

All descriptions for any of these tags should be a full sentence ending with a full stop (a period, for example).

#### Changes Warranting a Changelog Entry

Most code changes warrant a changelog entry to be recorded for the element but there are some exceptions.

+ **Classes**: Any breaking changes, deprecations, or the introduction of new class elements (elements which do not have their own changelog, such as class properties) require an accompanying `@since` tag entry. Changes to a class method should be recorded on the method's changelog, not on the class changelog.
+ **Functions and class methods**: Any change made requires an accompanying `@since` tag entry

Changes which do not affect the functionality or execution of the element *should not* be recorded on the element's changelog. For example, a coding standards change such as alignment or spacing should not be recorded.

#### Recording the Version Number

Versions should be expressed in the 3-digit `x.x.x` style.

```
 * @since 3.29.0
```

When any change has been made to the element an additional `@since` tag can be added with a short description of the changes which were made.

```
 * @since 3.3.0
 * @since 3.5.0 Added optional 3rd argument.
```

#### Deprecations

When an element is marked for deprecation this should be recorded at the end of the changelog with an `@deprecated` tag.

A short description may be added to provide additional information about the deprecation. If a replacement function has been added in it's place, note as much with an `@see` tag.

```
 * @since 3.3.0
 * @since 3.5.0 Added optional 3rd argument.
 * @deprecated 3.10.0 Use `llms_new_function_name()` instead.
 *
 * @see llms_new_function_name()
```

When adding documentation on an existing element which does not yet have a changelog (common in code added prior to the creation and enforcement of these standards) if it is impossible to determine when the element was added the version may be expressed with `Unknown` instead of the `x.x.x` version number.

#### File Headers

Whenever an element within a file is updated, the `@version` tag in the header should be updated to the current version of the codebase.

#### Tag alignment and order

All changelog tags, `@since`, `@version`, and `@deprecated` should be grouped together with a space before the first `@since` tag and after the last tag in the group.

```
 * @since 3.3.0
 * @since 3.5.0 Changelog entry description.
 * @deprecated 3.10.0 Use `llms_new_function_name()` instead.
```

When multiple lines are required for a single entry, subsequent lines should be indented to match the starting point of the description.

```
 * @since 3.3.0
 * @since 3.5.0 Changelog entry description.
                A second entry aligned to with the first entry.
```

Multiple logs with version numbers of differing lengths should not be aligned to one another.

```
 * @since 3.3.0
 * @since 3.25.0 Changelog entry description.
 * @since 4.0.0 This entry should not be aligned with the 3.25.0 entry above it.
```

#### Using Placeholders

When contributing code we recommend using the placeholder `[version]` in favor of trying to guess what version the element will be released with.

Our release workflow automatically replaces with `@since`, `@version`, and `@deprecated` followed by `[version]` with the actual version of the release being packaged.

For a new element:

```
 * @since [version]
```

When updating an existing element:

```
 * @since 3.5.0
 * @since [version] Updated element.
```


### Additional Tags

#### 1. Parameters and Returns

Functions and methods should define all parameter arguments and returns with the `@param` and `@return` tags.

No HTML should be used in the descriptions for these tags, though limited Markdown can be used as necessary, such as for adding backticks around variables, e.g. `$variable`.

All descriptions for any of these tags should be a full sentence ending with a full stop (a period, for example).

```
 * @param string $var1 Description of the argument.
 * @param bool $var2 Description of the argument.
 * @return string
 */
function my_function( $var1, $var2 = false ) {
    ...
    return $var1;
}
```

Parameters that are arrays should be documented using WordPress’ flavor of hash notation style, each array value beginning with the `@type` tag, and and describing the value as follows:

```
 *     @type type $key Description. Default 'value'. Accepts 'value', 'value'.
 *                     (aligned with Description, if wraps to a new line)
```

A full array parameter would look like this:

```
 * @param array $args {
 *     Optional. An array of arguments.
 *
 *     @type type $key Description. Default 'value'. Accepts 'value', 'value'.
 *                     (aligned with Description, if wraps to a new line)
 *     @type type $key Description.
 * }
```

#### 2. Types

Variables, constants, and class members should use the `@var` tag to describe the member's type.

```
 * @var string
 */
public $var = 'text';
```

#### 3. Relations and References

Use `@see` to perform automatic links to other areas of the codebase. For example `{@see 'is_lifterlms'}` to link to the filter `is_lifterlms`.


#### 4. Thrown Exceptions

A function or method which throws an exception should document the thrown exception using an `@throws` tag.

When present, the `@throws` tag should be added to the end of the docblock below the `@return` tag. An empty line should separate the `@return` and `@throws` tag.

```
 * @return string
 *
 * @throws Exception A description of the raised exception.
 */
```

## DocBlock Examples


### Functions and Class Methods

Functions and class methods should be formatted as follows:

+ Summary
+ Description (optional)
+ Changelog
+ Links and References (where appropriate)
+ Parameters
+ Return

```
/**
 * Summary.
 *
 * Description.
 *
 * @since x.x.x
 * @since x.x.x Description of function/method changes.
 *
 * @see Function/method/class relied on
 * @link URL
 *
 * @param type $var Description.
 * @param type $var Optional. Description. Default.
 * @return type Description.
 */
```


### Classes

Class DocBlocks should be formatted as follows:

+ Summary
+ Description (Optional)
+ Links and References (as an example use `@see` to reference a super class when documenting a sub class)
+ Changelog

```
/**
 * Summary.
 *
 * Description.
 *
 * @see Super_Class
 *
 * @since x.x.x
 * @since x.x.x Description of class changes.
 */
```


### Class Members

Class properties and constants should be formatted as follows:

+ Summary
+ Changelog
+ Type

```
/**
 * Summary.
 *
 * @since x.x.x
 * @since x.x.x Description of member changes.
 * @var type Optional description.
 */
```


### Hooks (Actions and Filters)

Both action and filter hooks should be documented on the line immediately preceding the call to `do_action()` or `do_action_ref_array()`, `apply_filters()`, or `apply_filters_ref_array()`, and formatted as follows:

+ Summary
+ Description (Optional)
+ Changelog
+ Parameters

Note that `@return` is not used for hook documentation, because action hooks return nothing, and filter hooks always return their first parameter.

```
/**
 * Summary.
 *
 * Description.
 *
 * @since x.x.x
 * @since x.x.x Description of hook changes.
 *
 * @param type  $var Description.
 * @param array $args {
 *     Short description about this hash.
 *
 *     @type type $var Description.
 *     @type type $var Description.
 * }
 * @param type  $var Description.
 */
```


### File Headers

The file header DocBlock is used to give an overview of what is contained in the file and should be formatted as follows:

+ Summary
+ Description (optional)
+ Links and references
+ Package
+ Changelog

```
/**
 * Summary (no period for file headers)
 *
 * Description. (use period)
 *
 * @link URL
 *
 * @package LifterLMS/SecondaryPackage/TertiaryPackage
 *
 * @since x.x.x
 * @since x.x.x Description of file changes.
 * @version x.x.x
 */
```


[llms-dev]: https://developer.lifterlms.com/reference/
[wp-core-docs]: https://developer.wordpress.org/coding-standards/inline-documentation-standards/
