<?php
class Template_Functions {
	
	//============================================================
	//functions for creating and setting up the template display, each template will call these functions
	//--------------------------
	public function wprevpro_get_media($review,$template_misc_array){	//get media and add to template
		$media='';
		//default this to turned on.
		if(!isset($template_misc_array['showmedia'])){
			$template_misc_array['showmedia']='yes';
		}
		if($template_misc_array['showmedia']=='yes'){
			$mediaurls = stripslashes($review->mediaurlsarrayjson);
			$mediathumburls = stripslashes($review->mediathumburlsarrayjson);
			$mediathumburlsarray = json_decode($mediathumburls, true);
			
			if(isset($mediaurls) && $mediaurls!=''){
				//turn back in to array then loop
				$mediaurlsarray = json_decode($mediaurls, true);
				if(is_array($mediaurlsarray)){
					$mediaurlsarray = array_filter($mediaurlsarray);
					if(count($mediaurlsarray)>0){
					$media='<div class="wprev_media_div '.count($mediaurlsarray).'">';
					$mediaurlsarray = array_values($mediaurlsarray);
					$n=0;
					foreach ($mediaurlsarray as &$urlvalue) {
						if($urlvalue!=""){
							$urlvalue = esc_url($urlvalue);
							//use thumbnail if we have it
							if(isset($mediathumburlsarray[$n]) && $mediathumburlsarray[$n]!=''){
								$thumburl = $mediathumburlsarray[$n];
							} else {
								$thumburl = $urlvalue;
							}
							$thumburl = esc_url($thumburl);
							//check if this is youtube video
							if(stripos($urlvalue,'youtu')===false){
								//not youtube
								$tempclass = 'notyoutu';
							} else {
								//is youtube
								$tempclass = 'youtu';
							}
							$media= $media . '<a class="wprev_media_img_a '.$tempclass.'" href="'.$urlvalue.'" data-lity><img src="'.$thumburl.'" class="wprev_media_img"  alt="media thumbnail '.$n.'"></a>';
						}
						$n++;
					}
					$media= $media . '</div>';
					}
				}
			}
		}
		return $media;
	}
	public function wprevpro_get_reviewername($review,$template_misc_array) {
		$tempreviewername = stripslashes(strip_tags($review->reviewer_name));
		$words = explode(" ", $tempreviewername);
		$firstname = $words[0];

		if(!isset($template_misc_array['lastnameformat'])){
			$template_misc_array['lastnameformat'] = 'show';
		}

		$tempreviewername = $firstname;
		if(isset($template_misc_array['lastnameformat'])){
			if($template_misc_array['lastnameformat']=="hide"){
				$tempreviewername=$firstname;
			} else if($template_misc_array['lastnameformat']=="initial"){
				$tempfirst = $firstname;
				if(isset($words[1])){
					$templast = $words[1];
					$templast =mb_substr($templast,0,1);
					$tempreviewername = $tempfirst.' '.$templast.'.';
				} else {
					$tempreviewername = $tempfirst;
				}
			} else {
				if(isset($words[1])){
				$templast = $words[1];
				} else {
					$templast = '';
				}
				$tempreviewername = $firstname. ' '.$templast;
			}
		}

		//add X/Twitter handle under the display name
		if($review->type=="Twitter"){
			$metaarray = json_decode($review->meta_data,true);
			if(isset($metaarray['screenname']) && $metaarray['screenname']!=''){
				$screename = sanitize_text_field($metaarray['screenname']);
				$tempreviewername = esc_html($tempreviewername)."<div class='wppro_twscrname'><a rel='nofollow noreferrer' target='_blank' href='https://x.com/".esc_attr($screename)."'>@".esc_html($screename)."</a></div>";
			}
		}
		
			return $tempreviewername;
	}

	/**
	 * Build a local initials avatar as a data-URI SVG (no external service).
	 *
	 * @param string $name Reviewer name.
	 * @param int    $size Pixel size.
	 * @return string data:image/svg+xml;base64,... URL
	 */
	public function wprev_get_initials_avatar_url( $name, $size = 100 ) {
		$name = trim( wp_strip_all_tags( (string) $name ) );
		$size = absint( $size );
		if ( $size < 1 ) {
			$size = 100;
		}
		if ( $size > 500 ) {
			$size = 500;
		}

		$words = preg_split( '/\s+/', $name );
		if ( is_array( $words ) && count( $words ) >= 2 ) {
			$initials = strtoupper( substr( $words[0], 0, 1 ) . substr( $words[ count( $words ) - 1 ], 0, 1 ) );
		} elseif ( $name !== '' ) {
			$initials = strtoupper( substr( $name, 0, 1 ) );
		} else {
			$initials = 'U';
		}

		$hash       = md5( $name !== '' ? $name : 'U' );
		$background = '#' . substr( $hash, 0, 6 );
		$r          = hexdec( substr( $hash, 0, 2 ) );
		$g          = hexdec( substr( $hash, 2, 2 ) );
		$b          = hexdec( substr( $hash, 4, 2 ) );
		$brightness = ( ( $r * 299 ) + ( $g * 587 ) + ( $b * 114 ) ) / 1000;
		if ( $brightness > 200 ) {
			$background = sprintf( '#%02x%02x%02x', max( 0, $r - 50 ), max( 0, $g - 50 ), max( 0, $b - 50 ) );
		}

		$font_size = (int) round( $size * 0.4 );
		$svg       = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
		$svg      .= '<rect width="100%" height="100%" fill="' . $background . '"/>';
		$svg      .= '<text x="50%" y="50%" dy=".1em" fill="#ffffff" font-family="Arial,Helvetica,sans-serif" font-size="' . $font_size . '" font-weight="bold" text-anchor="middle" dominant-baseline="middle">' . htmlspecialchars( $initials, ENT_QUOTES, 'UTF-8' ) . '</text>';
		$svg      .= '</svg>';

		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Escape an image src that may be https or a data URI.
	 *
	 * @param string $url Image URL.
	 * @return string
	 */
	public function wprev_esc_avatar_src( $url ) {
		if ( strpos( (string) $url, 'data:' ) === 0 ) {
			return esc_attr( $url );
		}
		return esc_url( $url );
	}

}
	
	//========================================
	
	?>