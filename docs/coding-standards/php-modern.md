Modern PHP Coding Standards
===========================

This standards exists for new LifterLMS add-ons which opt-in to the modern standard.

The goal of the modern standard is to require usage of less-archaic (though not necessarily bleeding edge) PHP code which are not found in the WordPress core coding standards and may be uncommon for many WordPress plugins and themes.

Our modern projects require PHP 7.4 or later and utilize namepsaces, [PSR-4 autoloading](https://www.php-fig.org/psr/psr-4/), strict typing, short array syntax, and other language features as described below.

## Ruleset

Name: `LifterLMS-Modern`
Base Rulesets: `WordPress`


## Rules

### Require Short Array Syntax

The PHP short array syntax is required.

Sniff: `Generic.Arrays.DisallowLongArraySyntax.Found`

```php
// Allowed indexed array.
$arr = [ 0, 1, 2 ];

// Allowed associative array.
$associative_arr = [
	'a' => 1,
	'b' => 2,
];


// Disallowed indexed array.
$long_arr = array( 0, 1, 2 );

// Disallowed associative array.
$associative_arr = array(
	'a' => 1,
	'b' => 2,
);
```

