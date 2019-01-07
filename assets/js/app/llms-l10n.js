/* global LLMS */

/**
 * Localization functions for LifterLMS Javascript
 *
 * @todo  we need more robust translation functions to handle sprintf and pluralization
 *        at this moment we don't need those and haven't stubbed them out
 *        those will be added when they're needed
 *
 * @type Object
 *
 * @since  2.7.3
 */
LLMS.l10n = LLMS.l10n || {};

LLMS.l10n.translate = function ( string ) {

	var self = this;

	if ( self.strings[string] ) {

		return self.strings[string];

	} else {

		return string;

	}

};

/**
 * Translate and replace placeholders in a string
 *
 * @example LLMS.l10n.replace( 'This is a %2$s %1$s String', {
 *           	'%1$s': 'cool',
 *    			'%2$s': 'very'
 *    		} );
 *    		Output: "This is a very cool String"
 *
 * @param    string   string        text string
 * @param    object   replacements  object containing token => replacement pairs
 * @return   string
 * @since    3.16.0
 * @version  3.16.0
 */
LLMS.l10n.replace = function( string, replacements ) {

	var str = this.translate( string );

	$.each( replacements, function( token, value ) {

		if ( -1 !== token.indexOf( 's' ) ) {
			value = value.toString();
		} else if ( -1 !== token.indexOf( 'd' ) ) {
			value = value * 1;
		}

		str = str.replace( token, value );

	} );

	return str;

};
