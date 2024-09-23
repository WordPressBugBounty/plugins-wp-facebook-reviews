(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	//document ready
	$(function(){


		//only show one review per a slide on mobile
		//get the attribute if it is set and this is in fact a slider
		$(".wprev-slider").each(function(){
			var oneonmobile = $(this).attr( "data-onemobil" );
			if(oneonmobile=='yes'){
				if (/Mobi|Android/i.test(navigator.userAgent) || $(window).width()<600) {
					/* this is a mobile device, continue */
					//get all the slider li elements, each li is a slide
					var li_elements_old = $(this).children('ul');
					console.log(li_elements_old);
					if(li_elements_old.length>0){
						//get array of all the divs containing the individual slide
						var divrevs = li_elements_old.find('.w3_wprs-col');
						var divrevarray = divrevs.get();
						//get the classes of the 2 divs under the li
						var div1class = divrevs.parent().attr('class');
						var div2class = divrevs.attr('class');
						console.log("div2class: "+div2class);
						//only continue if finding the divs
						if(typeof div2class !== "undefined"){
							//remove the l2, l3, l4, l5 , l6
							div2class = div2class.replace(/[a-z]\d\b/g, 'l12');
							//use the divrevarray to make new li elements with one review in each
							var newulhtml = '';
							var i;
							for (i = 0; i < divrevarray.length; i++) { 
								if(i==0){
									newulhtml += '<li class="wprs_unslider-active"><div class="'+div1class+'"><div class="'+div2class+'">'+divrevarray[i].innerHTML + '</div></div></li>';
								} else {
									newulhtml += '<li><div class="'+div1class+'"><div class="'+div2class+'">'+divrevarray[i].innerHTML + '</div></div></li>';
								}
							}
							//add the load more button if found
							if($(this).find('.wprevpro_load_more_div')[0]!== undefined){
								newulhtml += '<li>'+$(this).find('.wprevpro_load_more_div')[0].outerHTML+'</li>';
							}
							newulhtml +='';
							//replace the old li with the new
							li_elements_old.html(newulhtml);
							//re-initialize the slider if we need to
						}
					}
				}
			}
		});
		//}
		//----------------------
		
		
		$( ".wprs_rd_more" ).click(function() {
			$(this ).hide();
			$(this ).next("span").show(0, function() {
				// Animation complete.
				$(this ).css('opacity', '1.0');
			  });
			//$(this ).next("span").css('opacity', '1.0');
			
			//change height of wprev-slider-widget
			$(this ).closest( ".wprev-slider-widget" ).css( "height", "auto" );
			
			//change height of wprev-slider
			$(this ).closest( ".wprev-slider" ).css( "height", "auto" );

		});
			
		
			//check to see if we need to create slider;
			$( ".wprev-slider" ).each(function( index ) {
				createaslider(this,'shortcode');
			});
			$( ".wprev-slider-widget" ).each(function( index ) {
				createaslider(this,'widget');
			});
			function createaslider(thissliderdiv,type){
				
				var sliderhideprevnext = $(thissliderdiv).attr( "data-sliderhideprevnext" );
				var sliderhidedots = $(thissliderdiv).attr( "data-sliderhidedots" );
				var sliderautoplay = $(thissliderdiv).attr( "data-sliderautoplay" );
				var slidespeed = $(thissliderdiv).attr( "data-slidespeed" );
				var slideautodelay = $(thissliderdiv).attr( "data-slideautodelay" );
				var sliderfixedheight = $(thissliderdiv).attr( "data-sliderfixedheight" );
				var revsameheight = $(thissliderdiv).attr( "data-revsameheight" );
				
				var showarrows = true;
				if(sliderhideprevnext=="yes"){
					var showarrows = false;
				}
				var shownav = true;
				if(sliderhidedots=="yes"){
					var shownav = false;
				}
				var sautoplay = false;
				if(sliderautoplay=="yes"){
					var sautoplay = true;
				}
				var sspeed = parseFloat(slidespeed) * 1000;
				var sdelay = parseFloat(slideautodelay) * 1000;
				if(sdelay<sspeed){
					sdelay = sspeed;
				}
				var sanimate = true;
				if(sliderfixedheight=="yes"){
					sanimate = false;
				}
				//unhide other rows.
				$( thissliderdiv ).find('li').show();
				$( thissliderdiv ).wprs_unslider(
						{
						autoplay:false,
						infinite:false,
						delay: '3000',
						speed: '750',
						animation: 'horizontal',
						arrows: showarrows,
						animateHeight: true,
						activeClass: 'wprs_unslider-active',
						}
					);
				
				setTimeout(function(){ 
					//height of active slide
					var firstheight = $(thissliderdiv).find('.wprs_unslider-active').height();
					$(thissliderdiv).css( 'height', firstheight );
					$(thissliderdiv).find("li.wprevnextslide").removeClass('wprevnextslide');
				}, 500);
				
				if(sautoplay==true){
					slider.on('mouseover', function() {slider.data('wprs_unslider').stop();}).on('mouseout', function() {slider.data('wprs_unslider').start();});
				}
				
				//force height if set
				if(revsameheight=='yes'){
					var maxheights = $(thissliderdiv).find(".indrevdiv").map(function (){return $(this).outerHeight();}).get();
					var maxHeightofslide = Math.max.apply(null, maxheights);if(maxHeightofslide>0){$(thissliderdiv).find(".indrevdiv").css( "min-height", maxHeightofslide );}
				}
								
			};
		
		//simple tooltip for added elements and mobile devices
		$(".wprevpro_t1_outer_div").on('mouseenter touchstart', '.wprevtooltip', function(e) {
			var titleText = $(this).attr('data-wprevtooltip');
			$(this).data('tiptext', titleText).removeAttr('data-wprevtooltip');
			$('<p class="wprevpro_tooltip"></p>').text(titleText).appendTo('body').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px').fadeIn('slow');
		});
		$(".wprevpro_t1_outer_div").on('mouseleave touchend', '.wprevtooltip', function(e) {
			$(this).attr('data-wprevtooltip', $(this).data('tiptext'));
			$('.wprevpro_tooltip').remove();
		});
		$(".wprevpro_t1_outer_div").on('mousemove', '.wprevtooltip', function(e) {
			$('.wprevpro_tooltip').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px');
		});
		
	});

})( jQuery );
