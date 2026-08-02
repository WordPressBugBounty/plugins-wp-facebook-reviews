<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/public/partials
 */

 	//db function variables
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpfb_post_templates';
	
 //use the template id to find template in db, echo error if we can't find it or just don't display anything
 	//Get the form--------------------------
	$tid = htmlentities($a['tid']);
	$tid = intval($tid);
	$currentform = $wpdb->get_results("SELECT * FROM $table_name WHERE id = ".$tid);
	$template_misc_array = json_decode($currentform[0]->template_misc, true);
	
	//check to make sure template found
	if(isset($currentform[0])){
		
		//use values from currentform to get reviews from db
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		if($currentform[0]->hide_no_text=="yes"){
			$rlength = 1;
		} else {
			$rlength = 0;
		}
		
		if($currentform[0]->display_order=="random"){
			$sorttable = "RAND() ";
			$sortdir = "";
		} else {
			$sorttable = "created_time_stamp ";
			$sortdir = "DESC";
		}
		// Map saved rtype JSON to DB type values (Facebook / Twitter / both).
		$rtype_types = array( 'Facebook' );
		$rtype_decoded = json_decode( $currentform[0]->rtype, true );
		if ( is_array( $rtype_decoded ) ) {
			$rtype_types = array();
			if ( in_array( 'fb', $rtype_decoded, true ) || in_array( 'google', $rtype_decoded, true ) ) {
				// Keep legacy google token mapped to Facebook for this free plugin.
				if ( in_array( 'fb', $rtype_decoded, true ) ) {
					$rtype_types[] = 'Facebook';
				}
				if ( in_array( 'google', $rtype_decoded, true ) && ! in_array( 'Facebook', $rtype_types, true ) ) {
					$rtype_types[] = 'Google';
				}
			}
			if ( in_array( 'twitter', $rtype_decoded, true ) ) {
				$rtype_types[] = 'Twitter';
			}
			if ( empty( $rtype_types ) ) {
				$rtype_types = array( 'Facebook' );
			}
		} else {
			if ( $currentform[0]->rtype == '["fb"]' ) {
				$rtype_types = array( 'Facebook' );
			} elseif ( $currentform[0]->rtype == '["google"]' ) {
				$rtype_types = array( 'Google' );
			} elseif ( $currentform[0]->rtype == '["twitter"]' ) {
				$rtype_types = array( 'Twitter' );
			}
		}

		$reviewsperpage= $currentform[0]->display_num*$currentform[0]->display_num_rows;
		$tablelimit = $reviewsperpage;
		//change limit for slider
		if($currentform[0]->createslider == "yes"){
			$tablelimit = $tablelimit*$currentform[0]->numslides;
		}
		
				//min_rating filter----
		if($currentform[0]->min_rating>0){
			$min_rating = intval($currentform[0]->min_rating);
			//grab positive recommendations as well
			if($min_rating==1){
				$min_rating=0;
			}
			// Include positive recommendations and Twitter/X posts (no star rating).
			$ratingquery = " AND (rating >= '".$min_rating."' OR recommendation_type = 'positive' OR type = 'Twitter') ";
			
		} else {
			$min_rating ="";
			$ratingquery ="";
		}
		
		//location filter if set
		$sourcelocationfilter ="";
		if(isset($template_misc_array['filtersource']) && $template_misc_array['filtersource']!=""){
			$sourcelocationfilter = $wpdb->prepare( " AND pageid = %s", $template_misc_array['filtersource'] );
		}

		// Build type IN (...) clause from selected review types.
		$type_placeholders = implode( ', ', array_fill( 0, count( $rtype_types ), '%s' ) );
		
		//if we are hiding all reviews in badge settings then do not even look for them.
		//hide all the reviews!
		if(!isset($template_misc_array['bhreviews'])){
			$template_misc_array['bhreviews']='';
		}
		if($template_misc_array['bhreviews']=="yes"){
			$totalreviews = Array();
		} else {
			$prepare_args = array_merge( array( 0, $rlength ), $rtype_types, array( 'yes' ) );
			$totalreviews = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM ".$table_name."
				WHERE id>%d AND review_length >= %d AND type IN (".$type_placeholders.") AND hide != %s".$ratingquery.$sourcelocationfilter."
				ORDER BY ".$sorttable." ".$sortdir." 
				LIMIT ".$tablelimit." ", ...$prepare_args)
			);
		}

		//if we are adding a badge then wrap the slider in another outer div with flex box and add another div beside slider.
		if(!isset($template_misc_array['blocation'])){$template_misc_array['blocation']="";}
		
		if($template_misc_array['blocation']!=""){
			
			//preset in case this is an old template
			if(!isset($template_misc_array['bname'])){$template_misc_array['bname']='';}
			if(!isset($template_misc_array['bimgurl'])){$template_misc_array['bimgurl']='';}
			if(!isset($template_misc_array['bbtnurl'])){$template_misc_array['bbtnurl']='';}
			if(!isset($template_misc_array['bnameurl'])){$template_misc_array['bnameurl']='';}
			if(!isset($template_misc_array['bbtncolor'])){$template_misc_array['bbtncolor']='';}
			if(!isset($template_misc_array['bbkcolor'])){$template_misc_array['bbkcolor']='';}
			if(!isset($template_misc_array['bbradius'])){$template_misc_array['bbradius']='';}
			if(!isset($template_misc_array['bbwidth'])){$template_misc_array['bbwidth']='';}
			if(!isset($template_misc_array['bbcolor'])){$template_misc_array['bbcolor']='';}
			if(!isset($template_misc_array['bdropsh'])){$template_misc_array['bdropsh']='';}
			if(!isset($template_misc_array['bcenter'])){$template_misc_array['bcenter']='';}
			if(!isset($template_misc_array['bhname'])){$template_misc_array['bhname']='';}
			if(!isset($template_misc_array['bhphoto'])){$template_misc_array['bhphoto']='';}
			if(!isset($template_misc_array['bhbased'])){$template_misc_array['bhbased']='';}
			if(!isset($template_misc_array['bhbtn'])){$template_misc_array['bhbtn']='';}
			if(!isset($template_misc_array['filtersource'])){$template_misc_array['filtersource']='';}
			if(!isset($template_misc_array['bhpow'])){$template_misc_array['bhpow']='';}
			if(!isset($template_misc_array['bshape'])){$template_misc_array['bshape']='';}
			
			//get badge info
			$businessname = $template_misc_array['bname'];
			$imageurl = $template_misc_array['bimgurl'];
			$butnlinkurl = $template_misc_array['bbtnurl'];
			$bnameurl = $template_misc_array['bnameurl'];
			
			$bbtncolor = esc_html($template_misc_array['bbtncolor']);
			$bbackgroundcolor = esc_html($template_misc_array['bbkcolor']);
			$bborderradius = intval(esc_html($template_misc_array['bbradius']));
			$bborderwidth = absint($template_misc_array['bbwidth']);
			$bbordercolor = esc_html($template_misc_array['bbcolor']);
			$bdropsh = esc_html($template_misc_array['bdropsh']);
			$bcenter = esc_html($template_misc_array['bcenter']);	//center the image above and center text below.
			$bshape = esc_html($template_misc_array['bshape']);	//round or square
			
			$bhname = esc_html($template_misc_array['bhname']);	//hide the name
			$bhnameclass = "";
			if($bhname =="yes"){$bhnameclass = "badgehideclass";}
			
			$bhphoto = esc_html($template_misc_array['bhphoto']);	//hide the photo
			$bhphotoclass = "";
			if($bhphoto =="yes"){$bhphotoclass = "badgehideclass";}
			
			$bhbased = esc_html($template_misc_array['bhbased']);	//hide the based on text
			$bhbasedclass = "";
			if($bhbased =="yes"){$bhbasedclass = "badgehideclass";}
			
			$bhbtn = esc_html($template_misc_array['bhbtn']);	//hide the review us button
			$bhbtnclass = "";
			if($bhbtn =="yes"){$bhbtnclass = "badgehideclass";}
			
			$bhpow = esc_html($template_misc_array['bhpow']);	//hide the powered by
			$bhpowclass = "";
			if($bhpow =="yes"){$bhpowclass = "badgehideclass";}
			
		
			$badge_style = "";
			$badge_style = $badge_style . 'a.wprev-google-wr-a {background: '.$bbtncolor.' !important;}';
			$badge_style = $badge_style . 'a.wprev-google-wr-a:hover {background: '.$bbtncolor.'de !important;}';

			$badge_place_style = 'background: '.$bbackgroundcolor.' !important;border-radius:'.$bborderradius.'px !important;';
			if($bborderwidth > 0){
				if($bbordercolor === ''){
					$bbordercolor = '#eeeeee';
				}
				$badge_place_style .= 'border:'.$bborderwidth.'px solid '.$bbordercolor.' !important;';
			} else {
				$badge_place_style .= 'border:none !important;';
			}
			$badge_style = $badge_style . '.wprev-google-place {'.$badge_place_style.'}';
			if($bdropsh=="yes"){
				$badge_style = $badge_style . '.wprev-google-place {box-shadow: rgba(0, 0, 0, .08) 2px 2px 3px 0px !important;}';
			} else {
				$badge_style = $badge_style . '.wprev-google-place {box-shadow: none !important;}';
			}
			if($bcenter=="yes" && $template_misc_array['blocation']!="abovewide"){
				$badge_style = $badge_style . '.wprev-google-place {flex-direction: column !important;align-items: center !important;}';
				$badge_style = $badge_style . '.wprev-google-right {display: flex!important;align-items: center!important;flex-direction: column!important;}';
				$badge_style = $badge_style . '.wprev-google-name{margin-bottom: 3px !important;}';
			}
			if($bshape=="round"){
				$badge_style = $badge_style . 'img.sprev-google-left-src {border-radius: 50%;}';
			}
			
			//finally getting average and total here (computed straight from the reviews table).
			$templaceid = isset($template_misc_array['filtersource']) ? $template_misc_array['filtersource'] : '';
			$badgeavg = "";
			$badgetotal = "";
			if($templaceid!=""){
				$reviews_table = $wpdb->prefix . 'wpfb_reviews';
				$badge_type_placeholders = implode( ', ', array_fill( 0, count( $rtype_types ), '%s' ) );
				$badge_prepare_args = array_merge( array( $templaceid ), $rtype_types );
				$avgrow = $wpdb->get_row( $wpdb->prepare(
					"SELECT AVG(NULLIF(rating,0)) as avgr, COUNT(*) as totalr FROM ".$reviews_table." WHERE pageid = %s AND type IN (".$badge_type_placeholders.")",
					...$badge_prepare_args
				) );
				if($avgrow && $avgrow->totalr>0){
					$badgeavg = ($avgrow->avgr !== null) ? number_format((float)$avgrow->avgr, 1) : '';
					$badgetotal = intval($avgrow->totalr);
				}
			}

			//badge text overrides (Based on.. / Review us..).
			$bobasedon = isset($template_misc_array['bobasedon']) ? $template_misc_array['bobasedon'] : '';
			if($bobasedon!=''){
				$basedontext = esc_html(str_replace('#', $badgetotal, $bobasedon));
			} else {
				$basedontext = 'Based on <span class="wprev_btot">'.$badgetotal.'</span> reviews';
			}
			$default_borevus = in_array( 'Twitter', $rtype_types, true ) && ! in_array( 'Facebook', $rtype_types, true )
				? 'Find us on X!'
				: 'Review us on Facebook!';
			$borevustext = ( isset($template_misc_array['borevus']) && $template_misc_array['borevus']!='' ) ? $template_misc_array['borevus'] : $default_borevus;

			//if this is left mid then add a style
			if($template_misc_array['blocation']=="leftmid" || $template_misc_array['blocation']=="rightmid" ){
				$badge_style = $badge_style . '.wprev_outer_wb {align-items: center !important;}';
			}
			// Style 6 adds outer/card top margin; normalize when badge is beside slider.
			$wprev_outer_wb_class = 'wprev_outer_wb';
			if ( isset( $currentform[0]->style ) && (string) $currentform[0]->style === '6' ) {
				$badge_side_locations = array( 'left', 'right', 'leftmid', 'rightmid' );
				if ( in_array( $template_misc_array['blocation'], $badge_side_locations, true ) ) {
					$wprev_outer_wb_class .= ' wprev_badge_style6_side';
				}
			}
			//if this is above then we slightly change html again
			if($template_misc_array['blocation']=="above"){
				$badge_style = $badge_style . '.wprev_outer_wb {flex-direction: column !important;}.wprev_badge_div.badgeleft {margin-left: auto !important;margin-right: auto !important;}';
			}
			//if this is above and wide then we change html again
			$badgeabovewide1 = '';
			$badgeabovewide2 = '';
			$badgeabovewideclose ='';
			if($template_misc_array['blocation']=="abovewide"){
				$badge_style = $badge_style . '.wprev_outer_wb {flex-direction: column !important;}.wprev_badge_div.badgeleft {margin-left: auto !important;margin-right: auto !important;}.wprev_badge_div.badgeleft {margin: 0px 46px !important;}.wprev-google-place {justify-content: space-between !important;align-items: center !important;}.wprev-google-leftboth {display: flex;}';
				$badgeabovewide1 = '<div class="wprev-google-leftboth">';
				$badgeabovewide2 = '<div class="wprev-google-right">';
				$badgeabovewideclose = '</div>'; 
			}
			
			$bimgsize = 50;
			if(isset($template_misc_array['bimgsize']) &&  $template_misc_array['bimgsize']>0){
				$bimgsize =$template_misc_array['bimgsize'];
				$badge_style = $badge_style . 'img.sprev-google-left-src {min-width:'.$bimgsize.'px !important;min-height:'.$bimgsize.'px !important;}';
			}
			
			echo "<style>".$badge_style."</style>";
			echo '<div class="'.esc_attr( $wprev_outer_wb_class ).'">'; 
			

			
			$badgehtml = '<div class="wprev-google-place">'.$badgeabovewide1.'<div class="wprev-google-left '.$bhphotoclass.'"><img class="sprev-google-left-src" src="'.esc_url($imageurl).'" alt="'.esc_attr($businessname).'" width="'.$bimgsize.'" height="'.$bimgsize.'" title="'.esc_attr($businessname).'"></div><div class="wprev-google-right"><div class="wprev-google-name '.$bhnameclass.'"><a href="'.esc_url($bnameurl).'" target="_blank" rel="nofollow noopener"><span class="wprev-businessname">'.esc_html($businessname).'</span></a></div><div class="wprevstardiv"><span class="wprev-google-rating">'.$badgeavg.'</span><span class="wprevpro_star_imgs_T1"><span class="starloc1 wprevpro_star_imgs wprevpro_star_imgsloc1"><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span><span class="svgicons svg-wprsp-star"></span></span></span></div><div class="wprev-google-basedon '.$bhbasedclass.'">'.$basedontext.'</div>'.$badgeabovewideclose.$badgeabovewideclose.$badgeabovewide2.'<div class="wprev-google-powered '.$bhpowclass.'"><span class="wprev-google-powered-txt" style="font-size:12px;color:#777;">powered by Facebook</span></div><div class="wprev-google-wr '.$bhbtnclass.'"><a class="wprev-google-wr-a" rel="nofollow noopener" href="'.esc_url($butnlinkurl).'" onclick="">'.esc_html($borevustext).' <svg viewBox="0 0 512 512" height="18" width="18"><circle cx="256" cy="256" r="256" fill="#ffffff"></circle><path fill="#1877f2" d="M355.6 330.2l11.4-74.2h-71.2v-48.1c0-20.3 9.9-40.1 41.8-40.1h32.4V104.9s-29.4-5-57.5-5c-58.7 0-97 35.6-97 100v56.6h-65.2v74.2h65.2V512h80.3V330.2h59.5z"></path></svg></a></div></div></div>'.$badgeabovewideclose;
							
		}
			//actually adding badge html here for left and top
			if($template_misc_array['blocation']=="left" || $template_misc_array['blocation']=="leftmid" || $template_misc_array['blocation']=="above" || $template_misc_array['blocation']=="abovewide"){

				echo '<div class="wprev_badge_div badgeleft">';
				//this is where we could load badge styles in Pro version.
				
				echo $badgehtml;
					
				echo '</div>';
			}
			
	//only continue if some reviews found
	$makingslideshow=false;
	if(count($totalreviews)>0){
		//are we setting same height
		//need to pass this to javascript file
		$revsameheight = 'no';
		$notsameheight="revnotsameheight";
		if(isset($currentform[0]->review_same_height) && $currentform[0]->review_same_height!=""){
			if($currentform[0]->review_same_height=='yes'){
				$revsameheight = 'yes';
				$notsameheight="";
			}
		}	
		//if creating a slider than we need to split into chunks for each slider
		$totalreviewschunked = array_chunk($totalreviews, $reviewsperpage);
		
		//if making slide show then add it here
		if($currentform[0]->createslider == "yes"){
			//make sure we have enough to create a show here
			if($totalreviews>$reviewsperpage){
				$makingslideshow = true;
				$oneonmobile = "";
				if($currentform[0]->slidermobileview == "one"){
					$oneonmobile = "yes";
				}
				$sliderautoplay = "";
				$slidespeed = "";
				$slideautodelay = "";
				$sliderhideprevnext = "";
				$sliderhidedots = "";
				$sliderfixedheight = "";
				
				
				
				if(isset($template_misc_array['sliderautoplay'])){ $sliderautoplay = $template_misc_array['sliderautoplay'];}
				if(isset($template_misc_array['slidespeed'])){ $slidespeed = $template_misc_array['slidespeed'];}
				if(isset($template_misc_array['slideautodelay'])){ $slideautodelay = $template_misc_array['slideautodelay'];}
				if(isset($template_misc_array['sliderhideprevnext'])){ $sliderhideprevnext = $template_misc_array['sliderhideprevnext'];}
				if(isset($template_misc_array['sliderhidedots'])){ $sliderhidedots = $template_misc_array['sliderhidedots'];}
				if(isset($template_misc_array['sliderfixedheight'])){ $sliderfixedheight = $template_misc_array['sliderfixedheight'];}
				
				// Style 6 without a badge: tighter arrow inset (see .wprev_style6_nobadge CSS).
				$style6_nobadge = '';
				if ( (string) $currentform[0]->style === '6' && $template_misc_array['blocation'] === '' ) {
					$style6_nobadge = ' wprev_style6_nobadge';
				}

				//sliderautoplay,slidespeed,slideautodelay
				echo '<div class="wprev-slider '.$notsameheight.$style6_nobadge.'" id="wprev-slider-'.esc_html($currentform[0]->id).'" data-revsameheight="'.$revsameheight.'" data-onemobil="'.$oneonmobile.'" data-sliderautoplay="'.esc_html($sliderautoplay).'"  data-slidespeed="'.esc_html($slidespeed).'" data-slideautodelay="'.esc_html($slideautodelay).'" data-sliderhideprevnext="'.esc_html($sliderhideprevnext).'" data-sliderhidedots="'.esc_html($sliderhidedots).'" data-sliderfixedheight="'.esc_html($sliderfixedheight).'"><ul>';
			}
		} else {
			echo '<div class="wprev-no-slider '.$notsameheight.'" id="wprev-slider-'.esc_html($currentform[0]->id).'">';
		}
		
					
			//add styles from template misc here
			if(is_array($template_misc_array)){
				$misc_style ="";
				
				//hide stars and/or date
				if($template_misc_array['showstars']=="no"){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprevpro_star_imgs_T'.$currentform[0]->style.' {display: none;}';
				}
				//if($template_misc_array['showdate']=="no"){	//doing this by not adding code now
				//	$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_showdate_T'.$currentform[0]->style.' {display: none;}';
				//}
				
				$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_bradius_T'.$currentform[0]->style.' {border-radius: '.$template_misc_array['bradius'].'px;}';
				if($template_misc_array['bgcolor1']!=''){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.' {background:'.$template_misc_array['bgcolor1'].';}';
				}
				if($template_misc_array['bgcolor2']!=''){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_bg2_T'.$currentform[0]->style.' {background:'.$template_misc_array['bgcolor2'].';}';
				}
				if($template_misc_array['tcolor1']!=''){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_tcolor1_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor1'].';}';
				}
				if($template_misc_array['tcolor2']!=''){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_tcolor2_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor2'].';}';
				}
				
				//style specific mods 	div > p
				if($currentform[0]->style=="1"){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.'::after{ border-top: 30px solid '.$template_misc_array['bgcolor1'].'; }';
				}
				if($currentform[0]->style=="2"){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_bg1_T'.$currentform[0]->style.' {border-bottom:3px solid '.$template_misc_array['bgcolor2'].'}';
				}
				if($currentform[0]->style=="3"){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_tcolor3_T'.$currentform[0]->style.' {text-shadow:'.$template_misc_array['tcolor3'].' 1px 1px 0px;}';
				}
				if($currentform[0]->style=="4"){
					$misc_style = $misc_style . '#wprev-slider-'.$currentform[0]->id.' .wprev_preview_tcolor3_T'.$currentform[0]->style.' {color:'.$template_misc_array['tcolor3'].';}';
				}
				// Read More link color
				if ( ! empty( $template_misc_array['read_more_color'] ) ) {
					$rmc = trim( (string) $template_misc_array['read_more_color'] );
					if ( preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $rmc )
						|| preg_match( '/^rgba?\(\s*[\d.]+\s*,\s*[\d.]+\s*,\s*[\d.]+\s*(,\s*[\d.]+\s*)?\)$/i', $rmc ) ) {
						$misc_style .= '#wprev-slider-' . $currentform[0]->id . ' .wprs_rd_more{color:' . $rmc . ';}';
					}
				}
				//------------------------
				echo "<style>".$misc_style."</style>";
			}
			//--------------------------
			

			//print out user style added
			echo "<style>".$currentform[0]->template_css."</style>";
		
		$loopnum = 1;
		foreach ( $totalreviewschunked as $reviewschunked ){
			//echo "loop1";
			$totalreviewstemp = $reviewschunked;
			
			//need to break $totalreviewstemp up based on how many rows, create an multi array containing them
			if($currentform[0]->display_num_rows>1 && count($totalreviewstemp)>$currentform[0]->display_num){
				//count of reviews total is greater than display per row then we need to break in to multiple rows
				for ($row = 0; $row < $currentform[0]->display_num_rows; $row++) {
					$n=1;
					foreach ( $totalreviewstemp as $tempreview ){
						//echo "<br>".$tempreview->reviewer_name;
						//echo $n."-".$row."-".$currentform[0]->display_num;
						if($n>($row*$currentform[0]->display_num) && $n<=(($row+1)*$currentform[0]->display_num)){
							$rowarray[$row][$n]=$tempreview;
						}
						$n++;
					}
				}
			} else {
				//everything on one row so just put in multi array
				$rowarray[0]=$totalreviewstemp;
			}
			
			 
			//if making slide show
			if($makingslideshow){
				if($loopnum==1){
					echo '<li>';
				} else {
					echo '<li class="wprevnextslide">';
				}
			}
		 
				//include the correct tid here
				if($currentform[0]->style=="1"){
				$iswidget=false;
					include(plugin_dir_path( __FILE__ ) . '/template_style_1.php');
				//require_once plugin_dir_path( __FILE__ ) . '/template_style_1.php';
				}
				if($currentform[0]->style=="6"){
				$iswidget=false;
					include(plugin_dir_path( __FILE__ ) . '/template_style_6.php');
				}
			
			//if making slide show then end loop here
			if($makingslideshow){
					echo '</li>';
			}
			$loopnum++;
		
		}	//end loop chunks
		//if making slide show then end it
		if($makingslideshow){
				echo '</ul></div>';

		} else {
		echo '</div>';
		}
	 
	}
				//actually adding badge html here for right side
			if($template_misc_array['blocation']=="right" || $template_misc_array['blocation']=="rightmid"){

				echo '<div class="wprev_badge_div badgeright">';
				//this is where we could load badge styles in Pro version.
				
				echo $badgehtml;
					
				echo '</div>';
			}
			
			//end badge div if we are adding one.
		if(isset($template_misc_array['blocation']) && $template_misc_array['blocation']!=""){
			echo '</div>';
		}
}
?>

