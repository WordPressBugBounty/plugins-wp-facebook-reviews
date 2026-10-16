(function( $ ) {
	'use strict';

	/**
	 * Admin JS for the Templates editor: tabbed settings, live style preview,
	 * server-side AJAX preview + save, badge helpers.
	 */

	//document ready
	$(function(){
		var prestyle = "";
		var isResettingColors = false;

		//color picker
		var myOptions = {
			change: function(event, ui){
				var color = ui.color.toString();
				var element = event.target;
				$( element ).val(color);
				if(isResettingColors){
					return;
				}
				changepreviewhtml();
			},
			clear: function() {}
		};
		$('.my-color-field').wpColorPicker(myOptions);

		var pluginsUrl = (typeof adminjs_script_vars !== 'undefined' && adminjs_script_vars.pluginsUrl) ? adminjs_script_vars.pluginsUrl : '';

		//---------- live preview sample content ----------
		var starhtml = '<span class="wprev_stars_wrap wprevpro_star_imgs_T1"><span class="starloc1 wprevpro_star_imgs wprevpro_star_imgsloc1"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></span></span>';
		var starhtmlt6 = '<span class="wprev_stars_wrap wprevpro_star_imgs_T6"><span class="starloc1 wprevpro_star_imgs wprevpro_star_imgsloc1"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></span></span>';
		var sampltext = 'This is a sample review. Hands down the best experience we have had! Awesome staff and service. We will gladly recommend this gem to all our friends!';
		var verified1 = '<span class="verifiedloc1 wprevpro_verified_svg wprevtooltip" data-wprevtooltip="Verified on Facebook"><span class="svgicons svg-wprsp-verified"></span></span>';
		var iconhref = pluginsUrl + '/public/partials/imgs/facebook_small_icon.png';
		var iconhtml = '<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t1_fb_logo siteicon">';
		var iconhtmlt6 = '<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t6_site_logo siteicon">';

		var imagehref = pluginsUrl + '/admin/partials/sample_avatar.jpg';
		var imagehrefmystery = pluginsUrl + '/admin/partials/fb_profile.jpg';
		var lastnamehtml = '<span id="wprev_lastname">Wilson</span>';
		var displayname = 'Josh '+lastnamehtml;
		var avatarimg = imagehref;
		var datehtml = '<span id="wprev_showdate">1/12/2017</span>';
		var quoteimg = pluginsUrl + '/admin/partials/testimonial_quote.png';
		var samplemediaimage = pluginsUrl + '/admin/partials/samplemediaimage.jpg';
		var productimg = pluginsUrl + '/admin/partials/default-product-image2.png';
		var displayname3 = '<div id="wprev_showname">'+displayname+'</div>';

		var style1html = '<div class="wprevpro_t1_outer_div w3_wprs-row-padding">	\
							<div class="wprevpro_t1_DIV_1 w3_wprs-col">	\
								<div class="wprevpro_t1_DIV_2 indrevdiv wprev_preview_tcolor1_T1 wprev_preview_bg1_T1 wprev_preview_bradius_T1">	\
									<p class="wprevpro_t1_P_3 wprev_preview_tcolor1_T1">'+starhtml+''+verified1+''+sampltext+'</p>	\
									<a href="#" target="_blank" rel="nofollow">'+iconhtml+'</a>	\
								</div><span class="wprevpro_t1_A_8"><img src="'+imagehref+'" alt="thumb" class="wprevpro_t1_IMG_4 wprev_avatar_opt"></span> <span class="wprevpro_t1_SPAN_5 wprev_preview_tcolor2_T1">'+displayname+'<br><span class="wprev_showdate_T1" id="wprev_showdate">1/12/2017</span> </span>	\
							</div>	\
							</div>';

		var style6html = '<div class="wprevpro_t6_outer_div w3_wprs-row wprevprodiv">	\
							<div class="wprevpro_t6_DIV_1 w3_wprs-col outerrevdiv">	\
								<div class="wpproslider_t6_DIV_1a">	\
									<div class="indrevdiv wpproslider_t6_DIV_2 wprev_preview_bg1_T6 wprev_preview_bradius_T6">	\
										<div class="wpproslider_t6_DIV_2_top">	\
											<div class="wpproslider_t6_DIV_3L"><img src="'+imagehref+'" alt="thumb" class="wpproslider_t6_IMG_2 wprev_avatar_opt"></div>	\
											<div class="wpproslider_t6_DIV_3">	\
												<div class="t6displayname wpproslider_t6_STRONG_5 wprev_preview_tcolor2_T6">'+displayname+'</div>	\
												<div class="wpproslider_t6_star_DIV">'+starhtmlt6+''+verified1+'</div>	\
												<div class="wpproslider_t6_SPAN_6 wprev_preview_tcolor2_T6"><span class="wprev_showdate_T6" id="wprev_showdate">1/12/2017</span></div>	\
											</div>	\
										</div>	\
										<div class="wpproslider_t6_DIV_4">	\
											<div class="indrevtxt wpproslider_t6_P_4 wprev_preview_tcolor1_T6">'+sampltext+'</div>	\
										</div>	\
										<div class="wpproslider_t6_DIV_3_logo"><a href="#" target="_blank" rel="nofollow">'+iconhtmlt6+'</a></div>	\
									</div>	\
								</div>	\
							</div>	\
							</div>';

	var style2html = '<div class="wprevpro_t2_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t2_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t2_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t2_IMG_2">	\
								<div class="wpproslider_t2_DIV_3">	\
									<p class="wpproslider_t2_P_4 wprev_preview_tcolor1">	\
										'+starhtml+''+verified1+''+sampltext+'		</p> <strong class="wpproslider_t2_STRONG_5 wprev_preview_tcolor2">'+displayname+'</strong> <span class="wpproslider_t2_SPAN_6 wprev_preview_tcolor2">'+datehtml+'</span>	\
										<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t2_site_logo siteicon">	\
								</div></div></div></div>';

		var style3html = '<div class="wprevpro_t3_outer_div w3_wprs-row-padding">	\
				<div class="wpproslider_t3_DIV_1 w3_wprs-col l12">	\
			<div class="wpproslider_t3_DIV_1a wprev_preview_bg2 wprev_preview_bradius">	\
				<div class="wpproslider_t3_DIV_2 wprev_preview_bg1 wprev_preview_tcolor2 wprev_preview_tcolor3">	\
					<div class="wpproslider_t3_avatar_div">	\
					<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t3_avatar">	\
					</div>	\
					'+displayname3+'</div>	\
				<p class="wpproslider_t3_P_3 wprev_preview_tcolor1"><img src="'+quoteimg+'" alt="" class="wpproslider_t3_quote">'+starhtml+''+verified1+''+sampltext+' '+datehtml+'</p>	\
				<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t3_site_logo siteicon">	\
			</div>	\
		</div>	\
		</div>';

		var style4html = '<div class="wprevpro_t4_outer_div w3_wprs-row-padding">	\
			<div class="wpproslider_t4_DIV_1 w3_wprs-col l12">	\
		<div class="wpproslider_t4_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<div class="wpproslider_t4_avatar_div">	\
			<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t4_IMG_2">	\
			</div>	\
			<h3 class="wpproslider_t4_H3_3 wprev_preview_tcolor1">'+displayname+'</h3>	\
			<span class="wpproslider_t4_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<p class="wpproslider_t4_P_5 wprev_preview_tcolor2">'+sampltext+'</p>	\
			<span class="wpproslider_t4_date wprev_preview_tcolor3">'+datehtml+'</span>	\
			<div><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t4_site_logo siteicon"></div>	\
		</div></div></div>';

		var style5html = '<div class="wprevpro_t5_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t5_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t5_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="wpproslider_t5_DIV_3L"><img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t5_IMG_2"><span class="wpproslider_t5_STRONG_5 wprev_preview_tcolor2">'+displayname+'</span></div>	\
								<div class="wpproslider_t5_DIV_3">	\
									<p class="wpproslider_t5_P_4 wprev_preview_tcolor1">	\
										'+starhtml+''+verified1+''+sampltext+'<span class="wpproslider_t5_SPAN_6 wprev_preview_tcolor2"> - '+datehtml+'</span></p> 	\
								</div>	\
								<div class="wpproslider_t5_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t5_site_logo wprevsiteicon siteicon"></div>	\
								</div></div></div>';

		var style7html = '<div class="wprevpro_t7_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t7_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t7_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="wpproslider_t7_DIV_2_top">	\
									<div class="wpproslider_t7_DIV_3L">	\
										<div class="wpproslider_t7_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t7_site_logo siteicon"></div>	\
										<div class="wpproslider_t7_star_DIV">'+starhtml+''+verified1+'</div>	\
									</div>	\
								</div>	\
								<div class="wpproslider_t7_DIV_4">	\
									<div class="wpproslider_t7_DIV_3">	\
										<p class="wpproslider_t7_P_4 wprev_preview_tcolor1">"'+sampltext+'"</p> 	\
									</div>	\
									<div class="wpproslider_t7_STRONG_5 wprev_preview_tcolor2 t7displayname">'+displayname+'</div>	\
									<div class="wpproslider_t7_SPAN_6 wprev_preview_tcolor2 t7datediv">'+datehtml+'</div>	\
								</div>	\
								</div></div></div>';

		var style8html = '<div class="wprevpro_t8_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t8_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t8_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t8_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t8_DIV_3">	\
											<div class="wpproslider_t8_STRONG_5 wprev_preview_tcolor2 t8displayname">'+displayname+'<span class="wpproslider_t8_SPAN_6 wprev_preview_tcolor2 t8datediv">'+datehtml+'</span></div>	\
											<div class="wpproslider_t8_star_DIV">'+starhtml+''+verified1+'</div>	\
											<div class="wpproslider_t8_DIV_4"><p class="wpproslider_t8_P_4 wprev_preview_tcolor1">	\
											'+sampltext+'</p> 	\
										</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t8_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t8_site_logo siteicon"></div>	\
								</div></div></div>';

		var style9html = '<div class=" wprevpro_t9_DIV_1 w3_wprs-col">	\
								<div class="wpproslider_t9_DIV_1a">	\
									<div class="wpproslider_t9_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t9_DIV_2_top">	\
										<div class="wpproslider_t9_DIV_3L"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wpproslider_t9_IMG_2 wprevpro_avatarimg"></div>	\
										<div class="wpproslider_t9_star_DIV wprevpro_star_imgs_T9">'+starhtml+''+verified1+'</div>	\
										<div class="wpprooutoffive">5 out of 5 stars</div><div class="wpproslider_t9_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t9_site_logo siteicon"></div>	\
									</div>	\
									<div class="wpproslider_t9_DIV_3">	\
											<div class="t9displayname wpproslider_t9_STRONG_5 wprev_preview_tcolor2">'+displayname+'</div>	\
											<div class="wpproslider_t9_SPAN_6 wprev_preview_tcolor2"><span class="wprev_showdate_T9 wprev_preview_tcolor2">'+datehtml+'</span></div>	\
									</div>	\
									<div class="indrevdiv wpproslider_t9_DIV_4">	\
											<p class="wpproslider_t9_P_4 wprev_preview_tcolor1">'+sampltext+'</p>	\
									</div></div></div></div>';

		var style10html = '<div class="wprevpro_t10_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t10_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t10_DIV_2 wprev_preview_bg1 wprev_preview_bradius">	\
									<div class="wpproslider_t10_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t10_DIV_3L"><img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t10_IMG_2"></div>	\
										<div class="wpproslider_t10_DIV_3">	\
											<div class="wpproslider_t10_STRONG_5 wprev_preview_tcolor2 t10displayname"><span class="t10_revname wprev_preview_tcolor1">'+displayname+'</span> left us a 5 star review</div>	\
											<div class="wpproslider_t10_star_DIV">'+starhtml+''+verified1+' <span class="t10_onsite"> on Facebook</span></div>	\
											<div class="wpproslider_t10_SPAN_6 t10datediv">'+datehtml+'</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t10_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t10_site_logo siteicon"></div>	\
								</div></div></div>';

		var style11html = '<div class="wprevpro_t11_outer_div w3_wprs-row-padding">	\
							<div class="wpproslider_t11_DIV_1 w3_wprs-col l12">	\
								<div class="wpproslider_t11_DIV_2 wpproslider_t11_has_woo_image wprev_preview_bg1 wprev_preview_bradius">	\
								<div class="mscpic-img-side"><div class="mscpic-img-body"><img src="'+productimg+'" class="miscpic-listing-image" alt="sample product image"></div></div>	\
									<div class="wpproslider_t11_DIV_2_top" style="line-height:24px;">	\
										<div class="wpproslider_t11_DIV_3">	\
											<div class="wpproslider_t11_STRONG_5 wprev_preview_tcolor2 t11displayname">'+displayname+'<span class="wpproslider_t11_SPAN_6 wprev_preview_tcolor2 t11datediv">'+datehtml+'</span></div>	\
											<div class="wpproslider_t11_star_DIV">'+starhtml+''+verified1+'</div>	\
											<div class="wpproslider_t11_DIV_4"><p class="wpproslider_t11_P_4 wprev_preview_tcolor1">	\
											'+sampltext+'</p> 	\
											<div class="miscpicdiv mpdiv_t11 wprev_preview_tcolor1"><div class="mscpic-body wprev_preview_tcolor1"><span>Sample Product Title</span></div></div>	\
										</div>	\
										</div>	\
									</div>	\
									<div class="wpproslider_t11_DIV_3_logo"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t11_site_logo siteicon"></div>	\
								</div></div></div>';

		var style12html = '<div class="wprevpro_t12_outer_div w3_wprs-row-padding">	\
			<div class="wpproslider_t12_DIV_1 w3_wprs-col l12">	\
		<div class="wpproslider_t12_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<span class="wpproslider_t12_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<p class="wpproslider_t12_P_5 wprev_preview_tcolor2">'+sampltext+'</p>	\
			<div class="wpproslider_t12_avatar_div">	\
			<img src="'+avatarimg+'" class="wprev_avatar_opt wpproslider_t12_IMG_2">	\
			</div>	\
			<div><h3 class="wpproslider_t12_H3_3 wprev_preview_tcolor1">'+displayname+'</h3></div>	\
			<div><span class="wpproslider_t12_date wprev_preview_tcolor3">'+datehtml+'</span></div>	\
			<div><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t12_site_logo siteicon"></div>	\
		</div></div></div>';

		var style13html = '<div class="wprevpro_t13_outer_div w3_wprs-col l4 outerrevdiv" style="width: 100%; display: inline-block;">	\
	<div class="wpproslider_t6_DIV_1a"><div class="indrevdiv wpproslider_t13_DIV_2 wprev_preview_bg1 wprev_preview_bradius"><div class="wpproslider_t13_DIV_4"><div class="indrevtxt wpproslider_t13_P_4 wprev_preview_tcolor1">	\
	<div class="wprev_preview_tcolor1">'+sampltext+'</div>	\
	</div></div>	\
		<div class="wpproslider_t13_DIV_2_bot">	\
			<div class="wpproslider_t6_DIV_3L"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wpproslider_t13_IMG_2 wprevpro_avatarimg"></div>	\
			<div class="wpproslider_t13_DIV_3 wprev_preview_tcolor2">	\
				<div class="t13displayname wpproslider_t6_STRONG_5 wprev_preview_tcolor2">'+displayname+'</div>	\
				<div class="wpproslider_t13_star_DIV"><span class="wprevpro_star_imgs_T13">'+starhtml+''+verified1+'</span>	\
			</div><div class="wpproslider_t13_SPAN_6 wprev_preview_tcolor2"><span class="wprev_showdate_T13">'+datehtml+'</span></div></div>	\
		</div><div class="wpproslider_t13_DIV_3_logo">	\
		<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t6_site_logo siteicon">	\
		</div></div></div></div>';

		var style14html = '<div class="wprevpro_t14_DIV_1 wprevpro_t14_outer_div outerrevdiv wprevpro_t14_DIV_1_preview">	\
			<div class="wprevpro_t14_DIV_1a">	\
				<div class="wprevpro_t14_DIV_2 wprev_preview_bradius indrevdiv">	\
				<div class="wprevpro_t14_image_wrapper" style="background-image: url('+samplemediaimage+');"> \
						<div class="wprevpro_t14_review_text_overlay wprev_preview_tcolor1 wprev_preview_bg1">	\
						<div class="indrevlineclamp wprev_preview_tcolor1">'+sampltext+'</div>	\
						</div></div>	\
					<div class="wprevpro_t14_footer indrevtxt wprev_preview_bg2 wprevpro_t14_SPAN_5 wprev_preview_tcolor2">	\
						<div class="wprevpro_t14_avatarimg"><span class="wprevpro_t14_A_8"><img src="'+avatarimg+'" alt="Avatar" class="wprev_avatar_opt wprevpro_t14_IMG_4 wprevpro_avatarimg"></span></div>	\
						<div class="wprevpro_t14_SPAN_5">	\
							<div class="wprevpro_t14_rname wprevpro_t14_name wprev_preview_tcolor2">'+displayname+'</div>	\
							<div class="wprevpro_star_imgs_T14">'+starhtml+''+verified1+'</div>	\
							<div class="wprevpro_t14_showdate wprevpro_t14_date wprev_preview_tcolor3">'+datehtml+'</div>	\
						</div>	\
						<img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t14_site_logo siteicon">	\
						</div>	\
				</div>	\
			</div>	\
		</div>';

		var style15html = '<div class="wpproslider_t15_DIV_1 w3_wprs-col l4 outerrevdiv" style="width: 100%; display: inline-block;">	\
		<div class="indrevdiv wpproslider_t15_DIV_1a wprev_preview_bg1 wprev_preview_bradius">	\
			<div class="wpproslider_t15_avatar_div"><img src="'+avatarimg+'" alt="Avatar" class="wpproslider_t15_IMG_2 wprevpro_avatarimg wprev_avatar_opt"></div>	\
			<div class="wpproslider_t15_H3_3 wprev_preview_tcolor1">'+displayname+'</div>	\
			<span class="wprev_showdate_T15 wprev_preview_tcolor3">'+datehtml+'</span>	\
			<span class="wpproslider_t15_SPAN_4">'+starhtml+''+verified1+'</span>	\
			<div class="indrevtxt wpproslider_t15_P_5 wprev_preview_tcolor2"><p class="wpproslider_t15_P_5 wprev_preview_tcolor2">'+sampltext+'</p></div>	\
			<div class="wprevt15_icon_div"><img id="wprev_showicon" src="'+iconhref+'" alt="Facebook" class="wprevpro_t15_site_logo siteicon"></div>	\
		</div></div>';
		
		changepreviewhtml();

		function buildInitialsAvatarDataUri(name, size){
			size = size || 100;
			name = (name || 'U').toString().trim();
			var words = name.split(/\s+/).filter(Boolean);
			var initials;
			if(words.length >= 2){
				initials = (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
			} else if(name){
				initials = name.charAt(0).toUpperCase();
			} else {
				initials = 'U';
			}
			var hash = 0;
			for(var i = 0; i < name.length; i++){
				hash = ((hash << 5) - hash) + name.charCodeAt(i);
				hash |= 0;
			}
			var r = (hash >> 16) & 255;
			var g = (hash >> 8) & 255;
			var b = hash & 255;
			if(((r * 299) + (g * 587) + (b * 114)) / 1000 > 200){
				r = Math.max(0, r - 50);
				g = Math.max(0, g - 50);
				b = Math.max(0, b - 50);
			}
			var bg = '#' + [r, g, b].map(function(v){
				var h = v.toString(16);
				return h.length === 1 ? '0' + h : h;
			}).join('');
			var fontSize = Math.round(size * 0.4);
			var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'+size+'" height="'+size+'" viewBox="0 0 '+size+' '+size+'">' +
				'<rect width="100%" height="100%" fill="'+bg+'"/>' +
				'<text x="50%" y="50%" dy=".1em" fill="#ffffff" font-family="Arial,Helvetica,sans-serif" font-size="'+fontSize+'" font-weight="bold" text-anchor="middle" dominant-baseline="middle">'+initials+'</text>' +
				'</svg>';
			return 'data:image/svg+xml;base64,' + btoa(svg);
		}

		//tooltip for the "Verified on..." badge in previews
		var wpfbTooltipRoots = "#wprevpro_template_preview, #wpfbr_preview_outer";
		$( wpfbTooltipRoots ).on('mouseenter touchstart', '.wprevtooltip', function(e) {
			var titleText = $(this).attr('data-wprevtooltip');
			$(this).data('tiptext', titleText).removeAttr('data-wprevtooltip');
			$('<p class="wprevpro_tooltip"></p>').text(titleText).appendTo('body').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px').fadeIn('slow');
		});
		$( wpfbTooltipRoots ).on('mouseleave touchend', '.wprevtooltip', function(e) {
			$(this).attr('data-wprevtooltip', $(this).data('tiptext'));
			$('.wprevpro_tooltip').remove();
		});
		$( wpfbTooltipRoots ).on('mousemove', '.wprevtooltip', function(e) {
			$('.wprevpro_tooltip').css('top', (e.pageY - 15) + 'px').css('left', (e.pageX + 10) + 'px');
		});

		//reset colors to default
		$( "#wprevpro_pre_resetbtn" ).click(function() {
			resetcolors();
		});
		function resetcolors(){
			isResettingColors = true;
			var templatenum = $( "#wprevpro_template_style" ).val();
			if(templatenum=='1'){
				$( "#wprevpro_template_misc_bradius" ).val('0');
				$( "#wprevpro_template_misc_bgcolor1" ).val('#ffffff');
				$( "#wprevpro_template_misc_bgcolor2" ).val('#ffffff');
				$( "#wprevpro_template_misc_tcolor1" ).val('#777777');
				$( "#wprevpro_template_misc_tcolor2" ).val('#555555');
				prestyle="";
				$('#wprevpro_template_misc_bgcolor1').iris('color', '#ffffff');
				$('#wprevpro_template_misc_bgcolor2').iris('color', '#ffffff');
				$( "#wprevpro_template_misc_tcolor1" ).iris('color','#777777');
				$( "#wprevpro_template_misc_tcolor2" ).iris('color','#555555');
			} else if(templatenum=='6'){
				$( "#wprevpro_template_misc_bradius" ).val('4');
				$( "#wprevpro_template_misc_bgcolor1" ).val('#fdfdfd');
				$( "#wprevpro_template_misc_bgcolor2" ).val('#ffffff');
				$( "#wprevpro_template_misc_tcolor1" ).val('#555555');
				$( "#wprevpro_template_misc_tcolor2" ).val('#555555');
				prestyle="";
				$('#wprevpro_template_misc_bgcolor1').iris('color', '#fdfdfd');
				$('#wprevpro_template_misc_bgcolor2').iris('color', '#ffffff');
				$( "#wprevpro_template_misc_tcolor1" ).iris('color','#555555');
				$( "#wprevpro_template_misc_tcolor2" ).iris('color','#555555');
			}
			isResettingColors = false;
			changepreviewhtml();
		}

		//on template style change
		$( "#wprevpro_template_style" ).change(function() {
			if($( "#edittid" ).val()==""){
				resetcolors();
			}
			changepreviewhtml();
		});

		//re-render preview when style-tab settings change
		$( "#wprevpro_template_misc_showstars, #wprevpro_template_misc_showdate, #wprevpro_template_misc_bradius, #wprevpro_template_misc_bgcolor1, #wprevpro_template_misc_tcolor1, #wprevpro_template_misc_tcolor2, #wprevpro_template_misc_showicon, #wprevpro_template_misc_avataropt, #wprevpro_template_misc_verified, #wprevpro_template_misc_lastname" ).change(function() {
			changepreviewhtml();
		});
		$( "#wprevpro_template_misc_tfont1, #wprevpro_template_misc_tfont2" ).on('change keyup', function() {
			changepreviewhtml();
		});

		//custom css change preview
		var lastValue = '';
		$("#wpfbr_template_css").on('change keyup paste mouseup', function() {
			if ($(this).val() != lastValue) {
				lastValue = $(this).val();
				changepreviewhtml();
			}
		});

		function isProOnlyTemplateStyle(val){
			return val !== '1' && val !== '6' && val !== '' && val != null;
		}
		function updateProStyleNote(templatenum){
			$( "#wprevpro_pro_save_note" ).hide();
			if(isProOnlyTemplateStyle(templatenum)){
				$( "#wprevpro_template_style" ).addClass('wprevpro-pro-selected');
				$( "#wprevpro_pro_style_note" ).show();
			} else {
				$( "#wprevpro_template_style" ).removeClass('wprevpro-pro-selected');
				$( "#wprevpro_pro_style_note" ).hide();
			}
		}
		function showProSaveNote(){
			var note = $( "#wprevpro_pro_save_note" );
			var msg = note.attr( "data-msg" ) || "Style %s is Pro version only, please select style 1 or 6.";
			var style = $( "#wprevpro_template_style" ).val();
			note.text( msg.replace( "%s", style ) ).css( "display", "inline" );
		}

		function changepreviewhtml(){
			var templatenum = $( "#wprevpro_template_style" ).val();
			var T = '_T'+templatenum;
			var bradius = $( "#wprevpro_template_misc_bradius" ).val();
			var bg1 = $( "#wprevpro_template_misc_bgcolor1" ).val();
			var bg2 = $( "#wprevpro_template_misc_bgcolor2" ).val();
			var tcolor1 = $( "#wprevpro_template_misc_tcolor1" ).val();
			var tcolor2 = $( "#wprevpro_template_misc_tcolor2" ).val();
			var tfont1 = $( "#wprevpro_template_misc_tfont1" ).val();
			var tfont2 = $( "#wprevpro_template_misc_tfont2" ).val();
			var avataropt = $( "#wprevpro_template_misc_avataropt" ).val();
			var verified = $( "#wprevpro_template_misc_verified" ).val();
			var lastname = $( "#wprevpro_template_misc_lastname" ).val();

			if(templatenum=='1'){
				prestyle = "<style>.wprevpro_t1_DIV_2.wprev_preview_bg1_T1::after{ border-top: 30px solid "+bg1+"; }</style>";
			} else {
				prestyle = "";
			}
			if($( "#wpfbr_template_css" ).val()!=""){
				prestyle += '<style>'+$( "#wpfbr_template_css" ).val()+'</style>';
			}

			var previewhtml = '';
			if(templatenum=='1'){
				previewhtml = style1html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='2'){
				previewhtml = style2html;
				$( ".wprevpre_bgcolor2" ).show();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='3'){
				previewhtml = style3html;
				$( ".wprevpre_bgcolor2" ).show();
				$( ".wprevpre_tcolor3" ).show();
			} else if(templatenum=='4'){
				previewhtml = style4html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).show();
			} else if(templatenum=='5'){
				previewhtml = style5html;
				$( ".wprevpre_bgcolor2" ).show();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='6'){
				previewhtml = style6html;
				$( ".wprevpre_bgcolor2" ).show();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='7'){
				previewhtml = style7html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='8'){
				previewhtml = style8html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='9'){
				previewhtml = style9html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='10'){
				previewhtml = style10html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='11'){
				previewhtml = style11html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='12'){
				previewhtml = style12html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).show();
			} else if(templatenum=='13'){
				previewhtml = style13html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).hide();
			} else if(templatenum=='14'){
				previewhtml = style14html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).show();
			} else if(templatenum=='15'){
				previewhtml = style15html;
				$( ".wprevpre_bgcolor2" ).hide();
				$( ".wprevpre_tcolor3" ).show();
			}
			$( "#wprevpro_template_preview" ).html(prestyle+previewhtml);
			updateProStyleNote(templatenum);

			//show/hide by select values
			if($( "#wprevpro_template_misc_showstars" ).val()=="no"){
				$( ".wprev_stars_wrap" ).hide();
			} else {
				$( ".wprev_stars_wrap" ).show();
			}
			if($( "#wprevpro_template_misc_showdate" ).val()=="no"){
				$( "#wprev_showdate" ).hide();
			} else {
				$( "#wprev_showdate" ).show();
			}
			if($( "#wprevpro_template_misc_showicon" ).val()=="no"){
				$( "#wprev_showicon" ).hide();
			} else {
				$( "#wprev_showicon" ).show();
			}

			//colors + radius on the currently shown style classes
			if(!isProOnlyTemplateStyle(templatenum)){
				$( '.wprev_preview_bradius'+T ).css( "border-radius", bradius+'px' );
				if(bg1){
					$( '.wprev_preview_bg1'+T ).css( "background", bg1 );
				}
				if(bg2){
					$( '.wprev_preview_bg2'+T ).css( "background", bg2 );
				}
				if(tcolor1){
					$( '.wprev_preview_tcolor1'+T ).css( "color", tcolor1 );
				}
				if(tcolor2){
					$( '.wprev_preview_tcolor2'+T ).css( "color", tcolor2 );
				}
				if(tfont1 > 0){
					$( '.wprev_preview_tcolor1'+T ).css( {"font-size": tfont1+"px", "line-height": "normal"} );
				} else {
					$( '.wprev_preview_tcolor1'+T ).css( {"font-size": "", "line-height": ""} );
				}
				if(tfont2 > 0){
					$( '.wprev_preview_tcolor2'+T ).css( {"font-size": tfont2+"px", "line-height": "normal"} );
				} else {
					$( '.wprev_preview_tcolor2'+T ).css( {"font-size": "", "line-height": ""} );
				}
			} else {
				$( '.wprev_preview_bradius' ).css( "border-radius", bradius+'px' );
			}

			//avatar option
			if(avataropt=='hide'){
				$( ".wprev_avatar_opt" ).hide();
				if(templatenum=='6'){ $( ".wpproslider_t6_DIV_3L" ).hide(); }
				if(templatenum=='5'){ $( ".wpproslider_t5_DIV_3L" ).hide(); }
				if(templatenum=='10'){ $( ".wpproslider_t10_DIV_3L" ).hide(); }
			} else if(avataropt=='mystery'){
				$(".wprev_avatar_opt").attr("src",imagehrefmystery).show();
				if(templatenum=='6'){ $( ".wpproslider_t6_DIV_3L" ).show(); }
				if(templatenum=='5'){ $( ".wpproslider_t5_DIV_3L" ).show(); }
				if(templatenum=='10'){ $( ".wpproslider_t10_DIV_3L" ).show(); }
			} else if(avataropt=='init'){
				$(".wprev_avatar_opt").attr("src", buildInitialsAvatarDataUri('Josh Wilson')).show();
				if(templatenum=='6'){ $( ".wpproslider_t6_DIV_3L" ).show(); }
				if(templatenum=='5'){ $( ".wpproslider_t5_DIV_3L" ).show(); }
				if(templatenum=='10'){ $( ".wpproslider_t10_DIV_3L" ).show(); }
			} else {
				$(".wprev_avatar_opt").attr("src",imagehref).show();
				if(templatenum=='6'){ $( ".wpproslider_t6_DIV_3L" ).show(); }
				if(templatenum=='5'){ $( ".wpproslider_t5_DIV_3L" ).show(); }
				if(templatenum=='10'){ $( ".wpproslider_t10_DIV_3L" ).show(); }
			}

			//verified badge
			if(verified=='yes1'){
				$( ".verifiedloc1" ).show();
			} else {
				$( ".verifiedloc1" ).hide();
			}

			//last name format
			if(lastname=="hide"){
				$( "#wprev_lastname" ).hide();
				$(".t6displayname").html('Josh');
			} else if(lastname=="initial"){
				$("#wprev_lastname").html("W.").show();
				$(".t6displayname").html('Josh <span id="wprev_lastname">W.</span>');
			} else {
				$("#wprev_lastname").html("Wilson").show();
				$(".t6displayname").html('Josh '+lastnamehtml);
			}
		}

		//help button clicked
		$( "#wpfbr_helpicon_posts" ).click(function() {
			openpopup("Tips", '<p>This page will let you create multiple Reviews Templates that you can then add to your Posts or Pages via a shortcode or template function.</p>', "");
		});
		//show description after clicking help icon next to a setting label
		$( ".wpfbr_helpicon_p" ).click(function() {
			$(this).closest('tr').find('p.description').toggleClass('wpfbr_desc_visible');
		});
		//display shortcode button
		$( ".wpfbr_displayshortcode" ).click(function() {
			var tid = $( this ).parent().attr( "templateid" );
			var ttype = $( this ).parent().attr( "templatetype" );
			if(ttype=="widget"){
				openpopup("Widget Instructions", '<p>To display this in your Sidebar or other Widget areas, add the WP Reviews widget under Appearance > Widgets, and then select this template in the drop down.</p>', '');
			} else {
				openpopup("How to Display", '<p>Enter this shortcode on a post, page, or text widget: </br></br>[wprevpro_usetemplate tid="'+tid+'"]</p><p>Or you can add the following php code to your template: </br></br><code> do_action( \'wprev_pro_plugin_action\', '+tid+' ); </code></p>', '');
			}
		});

		//launch pop-up windows
		function openpopup(title, body, body2){
			jQuery( "#popup_titletext").html(title);
			jQuery( "#popup_bobytext1").html(body);
			jQuery( "#popup_bobytext2").html(body2);
			var popup = jQuery('#popup_review_list').popup({
				width: 400,
				offsetX: -100,
				offsetY: 0,
			});
			popup.open();
			var bodyheight = Number(jQuery( ".popup-content").height()) + 10;
			jQuery( "#popup_review_list").height(bodyheight);
		}

		//get the url parameter
		function getParameterByName(name, url) {
			if (!url) { url = window.location.href; }
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return decodeURIComponent(results[2].replace(/\+/g, " "));
		}

		//hide or show new template form
		var checkedittemplate = getParameterByName('taction');
		if(checkedittemplate=="edit"){
			jQuery("#wpfbr_new_template").show("slow");
			checkwidgetradio();
			showtemplatepreview();
		} else {
			jQuery("#wpfbr_new_template").hide();
		}
		$( "#wpfbr_addnewtemplate" ).click(function() {
			jQuery("#wpfbr_new_template").show("slow");
		});
		$( "#wpfbr_addnewtemplate_cancel" ).click(function() {
			jQuery("#wpfbr_new_template").hide("slow");
			setTimeout(function(){
				window.location.href = "?page=wpfb-templates_posts";
			}, 500);
		});

		//------- Server-side preview + save -------
		function createaslider(thissliderdiv, type){
			var $slider = $( thissliderdiv );
			if(typeof $slider.wprs_unslider !== 'function'){
				return;
			}
			var sliderhideprevnext = $slider.attr( "data-sliderhideprevnext" );
			var sliderhidedots = $slider.attr( "data-sliderhidedots" );
			var sliderautoplay = $slider.attr( "data-sliderautoplay" );
			var slidespeed = $slider.attr( "data-slidespeed" );
			var slideautodelay = $slider.attr( "data-slideautodelay" );
			var sliderfixedheight = $slider.attr( "data-sliderfixedheight" );
			var revsameheight = $slider.attr( "data-revsameheight" );

			var showarrows = true;
			if(type=='widget'){ showarrows = false; }
			if(sliderhideprevnext=="yes"){ showarrows = false; }
			var shownav = true;
			if(sliderhidedots=="yes"){ shownav = false; }
			var sautoplay = false;
			if(sliderautoplay=="yes"){ sautoplay = true; }
			var sspeed = parseFloat(slidespeed) * 1000;
			if(isNaN(sspeed) || sspeed<=0){ sspeed = 750; }
			var sdelay = parseFloat(slideautodelay) * 1000;
			if(isNaN(sdelay) || sdelay<=0){ sdelay = 5000; }
			if(sdelay<sspeed){ sdelay = sspeed; }
			var sanimate = true;
			if(sliderfixedheight=="yes"){ sanimate = false; }

			$slider.find('li').show();
			var slider = $slider.wprs_unslider({
				autoplay:sautoplay,
				infinite:false,
				delay: sdelay,
				speed: sspeed,
				animation: 'horizontal',
				arrows: showarrows,
				nav: shownav,
				animateHeight: sanimate,
				activeClass: 'wprs_unslider-active'
			});
			if(sanimate==true){
				setTimeout(function(){
					var firstheight = $slider.find('.wprs_unslider-active').height();
					$slider.css( 'height', firstheight );
				}, 500);
			}
			if(sautoplay==true){
				slider.on('mouseover', function() {slider.data('wprs_unslider').stop();}).on('mouseout', function() {slider.data('wprs_unslider').start();});
			}
			if(revsameheight=='yes'){
				var maxheights = $slider.find(".indrevdiv").map(function (){return $(this).outerHeight();}).get();
				var maxHeightofslide = Math.max.apply(null, maxheights);
				if(maxHeightofslide>0){ $slider.find(".indrevdiv").css( "min-height", maxHeightofslide ); }
			}
		}

		function initpreviewsliders(){
			$( "#wpfbr_preview_outer .wprev-slider" ).each(function(){
				createaslider(this, 'shortcode');
			});
			$( "#wpfbr_preview_outer .wprev-slider-widget" ).each(function(){
				createaslider(this, 'widget');
			});
			missingimgcheck();
			initPreviewLightbox();
		}

		function missingimgcheck(){
			$('#wpfbr_preview_outer img.wprev_media_img').each(function () {
				var img = this;
				var $img = $(this);
				function markMissing() {
					$img.addClass('wprev_missing_image');
				}
				if (img.complete) {
					if (img.naturalWidth === 0) {
						markMissing();
					}
					return;
				}
				$img.one('error', markMissing);
			});
		}

		function initPreviewLightbox(){
			var $preview = $('#wpfbr_preview_outer');
			if (!$preview.find('.wprev_media_div a.wprev_media_img_a').length) {
				return;
			}
			var lpluginsUrl = '';
			if (typeof wprevpublicjs_script_vars !== 'undefined' && wprevpublicjs_script_vars.wprevpluginsurl) {
				lpluginsUrl = wprevpublicjs_script_vars.wprevpluginsurl;
			} else if (pluginsUrl) {
				lpluginsUrl = pluginsUrl;
			}
			if (!lpluginsUrl) {
				return;
			}
			function bindMediaLightbox() {
				$preview.off('click.wprevlity', 'a.wprev_media_img_a').on('click.wprevlity', 'a.wprev_media_img_a', function(e) {
					e.preventDefault();
					e.stopImmediatePropagation();
					var href = $(this).attr('href');
					if (!href || typeof lity !== 'function') {
						return;
					}
					lity(href);
				});
			}
			function ensureLity(callback) {
				if (typeof lity === 'function') {
					callback();
					return;
				}
				if (!document.getElementById('wprev_lity_css')) {
					$('<link/>', {
						id: 'wprev_lity_css',
						rel: 'stylesheet',
						type: 'text/css',
						href: lpluginsUrl + '/public/css/lity.min.css'
					}).appendTo('head');
				}
				$.getScript(lpluginsUrl + '/public/js/lity.min.js', callback);
			}
			ensureLity(bindMediaLightbox);
		}

		function showtemplatepreview(){
			var tid = $( "#edittid" ).val();
			if(!tid || tid=='' || tid=='0'){
				return;
			}
			$( "#wpfbr_preview_outermost" ).show();
			$( "#loadingpreview" ).addClass('is-active');
			var senddata = {
				action: 'wpfb_get_preview',
				wpfb_nonce: adminjs_script_vars.wpfb_nonce,
				tid: tid
			};
			jQuery.post(ajaxurl, senddata, function(response){
				$( "#loadingpreview" ).removeClass('is-active');
				if(response){
					try {
						var result = JSON.parse(response);
						$( "#wpfbr_preview_outer" ).html(result.templatehtml);
						initpreviewsliders();
					} catch(e){
						alert('Error loading preview. Contact support. ' + e);
					}
				}
			});
		}

		//"Update": save via ajax then render returned preview
		$( "#wpfbr_addnewtemplate_update" ).click(function(e){
			e.preventDefault();
			if(isProOnlyTemplateStyle($( "#wprevpro_template_style" ).val())){
				$( "#wprevpro_pro_style_note" ).show();
				showProSaveNote();
				return;
			}
			if(jQuery( "#wpfbr_template_title").val()==""){
				alert("Please enter a title.");
				$( "#wpfbr_template_title" ).focus();
				return;
			}
			$( "#wpfbr_preview_outermost" ).show();
			$( "#savingformimg" ).addClass('is-active');
			$( "#update_form_msg" ).hide();

			var formArray = $( "#newtemplateform" ).serializeArray();
			var returnArray = {};
			for (var i = 0; i < formArray.length; i++){
				returnArray[formArray[i]['name']] = formArray[i]['value'];
			}
			var jsonfields = JSON.stringify(returnArray);
			var senddata = {
				action: 'wpfb_save_template',
				wpfb_nonce: adminjs_script_vars.wpfb_nonce,
				data: jsonfields
			};
			jQuery.post(ajaxurl, senddata, function(response){
				$( "#savingformimg" ).removeClass('is-active');
				if(response){
					try {
						var saveresult = JSON.parse(response);
						if(saveresult.ack=='success'){
							$( "#update_form_msg" ).show();
							if(saveresult.iu=='insert'){
								$( "#edittid" ).val(saveresult.t_id);
							}
							$( "#wpfbr_preview_outer" ).html(saveresult.templatehtml);
							initpreviewsliders();
						} else {
							alert('Error saving/updating template. Please contact support. ' + saveresult.ackmessage);
						}
					} catch(e){
						alert('Error saving/updating template. Contact support. ' + e);
					}
				} else {
					alert('Error saving/updating template. Please contact support.');
				}
				setTimeout(function(){ $( "#update_form_msg" ).hide(); }, 2500);
			});
		});

		//read-more toggle inside preview
		$( "#wpfbr_preview_outer" ).on( "click", ".wprs_rd_more", function(){
			$(this).hide();
			$(this).next("span").show(0, function(){ $(this).css('opacity','1.0'); });
			$(this).closest( ".wprev-slider-widget" ).css( "height", "auto" );
			$(this).closest( ".wprev-slider" ).css( "height", "auto" );
		});

		//form validation (Save & Close)
		$("#newtemplateform").submit(function(){
			if(isProOnlyTemplateStyle($( "#wprevpro_template_style" ).val())){
				$( "#wprevpro_pro_style_note" ).show();
				showProSaveNote();
				return false;
			}
			if(jQuery( "#wpfbr_template_title").val()==""){
				alert("Please enter a title.");
				$( "#wpfbr_template_title" ).focus();
				return false;
			}
			return true;
		});

		//widget radio clicked
		$('input[type=radio][name=wpfbr_template_type]').change(function() {
			checkwidgetradio();
		});
		function checkwidgetradio() {
			var widgetvalue = $("input[name=wpfbr_template_type]:checked").val();
			if (widgetvalue == 'widget') {
				$('#wpfbr_t_display_num').val("1");
				$('#wpfbr_t_display_num').hide();
				$('#wpfbr_t_display_num').prev().hide();
			} else if (widgetvalue == 'post') {
				if($('#edittid').val()==""){
					$('#wpfbr_t_display_num').val("3");
				}
				$('#wpfbr_t_display_num').show();
				$('#wpfbr_t_display_num').prev().show();
			}
		}

		//------------Template settings tabs (Style / General / Filter / Badge / AI Summary)------------
		var currenttab = 0;
		function hideAllSettingTables(){
			$( "#settingtable0, #settingtable1, #settingtable2, #settingtable3, #settingtable4" ).hide();
		}
		$( ".gotopage0" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable0" ).fadeIn();
			currenttab = 0; changecurrenttab(currenttab);
		});
		$( ".gotopage1" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable1" ).fadeIn();
			currenttab = 1; changecurrenttab(currenttab);
		});
		$( ".gotopage2" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable2" ).fadeIn();
			currenttab = 2; changecurrenttab(currenttab);
		});
		$( ".gotopage3" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable3" ).fadeIn();
			currenttab = 3; changecurrenttab(currenttab);
		});
		$( ".gotopage4" ).click(function() {
			hideAllSettingTables();
			$( "#settingtable4" ).fadeIn();
			currenttab = 4; changecurrenttab(currenttab);
		});
		function changecurrenttab(ctab){
			$( ".settingtab" ).removeClass( "nav-tab-active" );
			$( "#settingtab"+ctab ).addClass("nav-tab-active");
		}

		//------------Badge settings: show/hide options when Location is set------------
		hideshowbadgeoptions(true);
		$( "#wpfbr_t_blocation" ).change(function() {
			hideshowbadgeoptions(false);
		});
		function hideshowbadgeoptions(instant){
			if($( "#wpfbr_t_blocation" ).val()==""){
				// Instant hide on load — animated hide on <tr> can leave table-row gaps
				if(instant){
					$( ".badgehide" ).hide();
				} else {
					$( ".badgehide" ).hide('slow');
				}
			} else {
				if(instant){
					$( ".badgehide" ).show();
				} else {
					$( ".badgehide" ).show('slow');
				}
			}
		}

		//------------Badge: fill name/URLs from Choose Source dropdown------------
		function setbadgetitle(){
			var $opt = $( "#wpfbr_t_filtersource option:selected" );
			if(!$opt.length || !$opt.val()){ return; }
			var sname = $opt.text();
			var surl = $opt.attr('data-fromurl') || '';
			if(sname && $( "#wpfbr_t_bname" ).val()==""){
				$( "#wpfbr_t_bname" ).val(sname);
			}
			if(surl){
				if($( "#wpfbr_t_bnameurl" ).val()=="" || $( "#wpfbr_t_bnameurl" ).val()=="https://www.facebook.com/"){
					$( "#wpfbr_t_bnameurl" ).val(surl);
				}
				if($( "#wpfbr_t_bbtnurl" ).val()=="" || $( "#wpfbr_t_bbtnurl" ).val()=="https://www.facebook.com/"){
					$( "#wpfbr_t_bbtnurl" ).val(surl);
				}
			}
		}
		$( "#wpfbr_t_filtersource" ).change(function() {
			setbadgetitle();
		});
		setbadgetitle();

		//------------Badge business image uploader (wp.media)------------
		var wpfbBadgeMediaFrame;
		$('#upload_licon_button').on('click', function(e) {
			e.preventDefault();
			if (typeof wp === 'undefined' || !wp.media) {
				return false;
			}
			if (wpfbBadgeMediaFrame) {
				wpfbBadgeMediaFrame.open();
				return false;
			}
			wpfbBadgeMediaFrame = wp.media({
				title: 'Upload Icon',
				button: { text: 'Use this image' },
				library: { type: 'image' },
				multiple: false
			});
			wpfbBadgeMediaFrame.on('select', function() {
				var attachment = wpfbBadgeMediaFrame.state().get('selection').first().toJSON();
				if (attachment && attachment.url) {
					$('#wpfbr_t_bimgurl').val(attachment.url);
				}
			});
			wpfbBadgeMediaFrame.open();
			return false;
		});

	});

})( jQuery );
