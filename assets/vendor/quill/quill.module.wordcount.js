/**
 * Quill Wordcount Module v1.0.0
 * https://github.com/gocodebox/quill-wordcount-module
 */
( function() {

	if ( 'undefined' === Quill ) {
		return;
	}

	/**
	 * i18n number formatter
	 * @param    int   number  number to format
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	function formatNumber( number ) {
		return new Intl.NumberFormat().format( number );
	}

	/**
	 * Create the wordcounter container element
	 * @param    obj   options  counter options
	 * @return   obj            JS DOM Element
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	function createContainer( options ) {

		var container = document.createElement( 'div' );

		if ( options.min ) {
			var min = document.createElement( 'i' );
			min.className = 'ql-wordcount-min';
			min.style.opacity = '0.5';
			min.style.marginRight = '10px';
			min.innerHTML = options.l10n.min + ': ' + formatNumber( options.min );
			container.appendChild( min );
		}

		if ( options.max ) {
			var max = document.createElement( 'i' );
			max.className = 'ql-wordcount-max';
			max.style.opacity = '0.5';
			max.innerHTML = options.l10n.max + ': ' + formatNumber( options.max );
			container.appendChild( max );
		}

		container.className = 'ql-wordcount ql-toolbar ql-snow';
		container.style.marginTop = '-1px';
		container.style.fontSize = '85%';

		return container;

	};

	/**
	 * Retrieve the formatted "N words" string
	 * @param    obj   l10n   localization text object
	 * @param    int   words  current wordcount
	 * @return   string
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	function getCounterText( l10n, words ) {

		var unit = l10n.plural;

		if ( 1 === words ) {
			unit = l10n.singluar;
		}

		return formatNumber( words ) + ' ' + unit;

	}

	/**
	 * Register the Quill wordcount module
	 * @param    obj   quill    quill instance object
	 * @param    obj   options  module options
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	Quill.register( 'modules/wordcount', function( quill, options ) {

		options.l10n = options.l10n || {
			singluar: 'word',
			plural: 'words',
			min: 'Minimum',
			max: 'Maximum',
		};

		var container = createContainer( options ),
			counter = document.createElement( 'span' );
		counter.className = 'ql-wordcount-counter';
		counter.style.float = 'right';

		container.appendChild( counter );

		/**
		 * Update the counter element color based on current word count & min/max settings
		 * @param    int   words  current word count
		 * @return   void
		 * @since    1.0.0
		 * @version  1.0.0
		 */
		function setCounterWarnings( words ) {

			var color = '';

			if ( options.min ) {

				if ( words < options.min ) {
					color = '#e5554e';
				}

			}

			if ( options.max ) {
				if ( words > options.max ) {
					color = '#e5554e';
				} else if ( words > options.max * 0.9 ) {
					color = '#ff922b';
				}
			}

			counter.style.color = color;

		}

		/**
		 * Set the text of the counter element
		 * @return   void
		 * @since    1.0.0
		 * @version  1.0.0
		 */
		function setCounterText() {

			var text = quill.getText(),
				match = text.match(/\S+/g),
				words = match ? text.match(/\S+/g).length : 0;

			setCounterWarnings( words );
			counter.innerHTML = getCounterText( options.l10n, words );

			if ( options.onChange ) {
				options.onChange( quill, options, words );
			}

		};

		setCounterText();

		quill.container.parentNode.insertBefore( container, quill.container.nextSibling );

		quill.on('text-change', setCounterText );

	} );

} )();
