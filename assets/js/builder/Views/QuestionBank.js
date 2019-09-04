/**
 * Quiz question bank view
 *
 * @since    3.16.0
 * @version  3.16.0
 */
define( [ 'Views/QuestionType' ], function( QuestionView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-question',

		/**
		 * Parent element
		 *
		 * @type  {String}
		 */
		el: '#llms-question-bank',

		/**
		 * Section model
		 *
		 * @type  {[type]}
		 */
		modelView: QuestionView,

		/**
		 * Are sections selectable?
		 *
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 *
		 * @type  {Bool}
		 */
		sortable: false,

	} );

} );
