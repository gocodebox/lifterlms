/**
 * Create a Donut Chart
 *
 * @package LifterLMS/Scripts
 *
 * @since 3.9.0
 * @version 4.15.0
 *
 * @link https://gist.github.com/joeyinbox/8205962
 *
 * @param {Object} $el jQuery element to draw a chart within.
 */

LLMS.Donut = function( $el ) {

	/**
	 * Constructor
	 *
	 * @since 3.9.0
	 * @since 4.15.0 Flip animation in RTL.
	 *
	 * @param {Object} options Donut options.
	 * @return {Void}
	 */
	function Donut(options) {

		this.settings = $.extend( {
			element: options.element,
			percent: 100
		}, options );

		this.circle                = this.settings.element.find( 'path' );
		this.settings.stroke_width = parseInt( this.circle.css( 'stroke-width' ) );
		this.radius                = ( parseInt( this.settings.element.css( 'width' ) ) - this.settings.stroke_width ) / 2;
		this.angle                 = $( 'body' ).hasClass( 'rtl' ) ? 82.5 : 97.5; // Origin of the draw at the top of the circle
		this.i                     = Math.round( 0.75 * this.settings.percent );
		this.first                 = true;
		this.increment             = $( 'body' ).hasClass( 'rtl' ) ? -5 : 5;

		this.animate = function() {
			this.timer = setInterval( this.loop.bind( this ), 10 );
		};

		this.loop = function() {
			this.angle += this.increment;
			this.angle %= 360;
			var radians = ( this.angle / 180 ) * Math.PI,
				x       = this.radius + this.settings.stroke_width / 2 + Math.cos( radians ) * this.radius,
				y       = this.radius + this.settings.stroke_width / 2 + Math.sin( radians ) * this.radius,
				d;
			if (this.first === true) {
				d          = this.circle.attr( 'd' ) + ' M ' + x + ' ' + y;
				this.first = false;
			} else {
				d = this.circle.attr( 'd' ) + ' L ' + x + ' ' + y;
			}
			this.circle.attr( 'd', d );
			this.i--;

			if (this.i <= 0) {
				clearInterval( this.timer );
			}
		};
	}

	/**
	 * Draw donut element
	 *
	 * @since 3.9.0
	 *
	 * @param {Object} $el jQuery element to draw a chart within.
	 * @return {Void}
	 */
	function draw( $el ) {
		var path = '<path d="M100,100" />';
		$el.append( '<svg preserveAspectRatio="xMidYMid" xmlns:xlink="http://www.w3.org/1999/xlink">' + path + '</svg>' );
		var donut = new Donut( {
			element: $el,
			percent: $el.attr( 'data-perc' )
		} );
		donut.animate();
	}

	draw( $el );

};
