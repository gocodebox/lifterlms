/**
 * Abstract LifterLMS Model
 * @since    [version]
 * @version  [version]
 */
define( [ 'Models/_Relationships', 'Models/_Utilities' ], function( Relationships, Utilities ) {

	return Backbone.Model.extend( _.defaults( {}, Relationships, Utilities ) );

} );
