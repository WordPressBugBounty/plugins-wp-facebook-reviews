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

		//add twitter handle
		if($review->type=="Twitter"){
			$metaarray = json_decode($review->meta_data,true);
			if(isset($metaarray['screenname'])){
			$screename = $metaarray['screenname'];
			$tempreviewername = $tempreviewername."<div class='wppro_twscrname'><a rel='nofollow noreferrer' target='_blank' href='https://twitter.com/".$screename."'>@".$screename."</a></div>";
			}
			
		}
		
			return $tempreviewername;
	}
	
	
	
}
	
	//========================================
	
	?>