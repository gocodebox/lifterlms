/**
 * Quiz question bank view
 * @since    [version]
 * @version  [version]
 */
define( [ 'Views/QuestionType' ], function( QuestionView ) {

	return Backbone.CollectionView.extend( {

		className: 'llms-question',

		/**
		 * Parent element
		 * @type  {String}
		 */
		el: '#llms-question-bank',

		/**
		 * Section model
		 * @type  {[type]}
		 */
		modelView: QuestionView,

		/**
		 * Are sections selectable?
		 * @type  {Bool}
		 */
		selectable: false,

		/**
		 * Are sections sortable?
		 * @type  {Bool}
		 */
		sortable: false,

	} );

} );
