/**
 * Abstract LifterLMS Model
 * @since    3.17.0
 * @version  3.17.0
 */
define( [ 'Models/_Relationships', 'Models/_Utilities' ], function( Relationships, Utilities ) {

	return Backbone.Model.extend( _.defaults( {}, Relationships, Utilities ) );

} );
