( function ( $ ) {
	'use strict';

	/**
	 * Start a review slider that was inserted after the page loaded.
	 *
	 * Mirrors the front-end initializer. Sliders that are already running are left alone.
	 *
	 * @param {HTMLElement} slider Slider root (.wprev-slider).
	 */
	function createSlider( slider ) {
		var $slider = $( slider );
		if ( $slider.data( 'wprs_unslider' ) ) {
			return;
		}

		var slidespeed = $slider.attr( 'data-slidespeed' );
		var slideautodelay = $slider.attr( 'data-slideautodelay' );
		var sspeed = parseFloat( slidespeed ) * 1000;
		var sdelay = parseFloat( slideautodelay ) * 1000;
		var sanimate = $slider.attr( 'data-sliderfixedheight' ) !== 'yes';

		if ( isNaN( sspeed ) ) {
			sspeed = 750;
		}
		if ( isNaN( sdelay ) ) {
			sdelay = 5000;
		}
		if ( sdelay < sspeed ) {
			sdelay = sspeed;
		}

		$slider.find( 'li' ).show();
		var instance = $slider.wprs_unslider( {
			autoplay: $slider.attr( 'data-sliderautoplay' ) === 'yes',
			infinite: false,
			delay: sdelay,
			speed: sspeed,
			animation: 'horizontal',
			arrows: $slider.attr( 'data-sliderhideprevnext' ) !== 'yes',
			nav: $slider.attr( 'data-sliderhidedots' ) !== 'yes',
			animateHeight: sanimate,
			activeClass: 'wprs_unslider-active'
		} );

		if ( sanimate ) {
			window.setTimeout( function () {
				var firstheight = $slider.find( '.wprs_unslider-active' ).height();
				$slider.css( 'height', firstheight );
				$slider.find( 'li.wprevnextslide' ).removeClass( 'wprevnextslide' );
			}, 500 );
		}

		if ( $slider.attr( 'data-revsameheight' ) === 'yes' ) {
			var maxheights = $slider.find( '.indrevdiv' ).map( function () {
				return $( this ).outerHeight();
			} ).get();
			var maxHeightofslide = Math.max.apply( null, maxheights );
			if ( maxHeightofslide > 0 ) {
				$slider.find( '.indrevdiv' ).css( 'min-height', maxHeightofslide );
			}
		}

		if ( instance && $slider.attr( 'data-sliderautoplay' ) === 'yes' ) {
			instance.on( 'mouseover', function () {
				instance.data( 'wprs_unslider' ).stop();
			} ).on( 'mouseout', function () {
				instance.data( 'wprs_unslider' ).start();
			} );
		}
	}

	window.wprevInitReviewSliders = function ( root ) {
		if ( ! $.fn || ! $.fn.wprs_unslider ) {
			return;
		}
		var $root = root ? $( root ) : $( document );
		$root.find( '.wprev-slider, .srfw-slider' ).each( function () {
			createSlider( this );
		} );
	};
}( window.jQuery ) );
