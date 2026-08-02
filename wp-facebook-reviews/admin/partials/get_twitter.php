<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ljapps.com
 * @since      1.0.0
 *
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/admin/partials
 */
 
 //add thickbox
 add_thickbox();

     // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
		
	$dbmsg = "";
	$html="";
	$currentgetappform= new stdClass();
	$currentgetappform->id="";
	$currentgetappform->title="";
	$currentgetappform->site_type="";
	$currentgetappform->query="";
	$currentgetappform->endpoint="";
	//$currentgetappform->cron="";
	//$currentgetappform->blocks="100";
	$currentgetappform->profile_img="";
	$currentgetappform->categories="";
	$currentgetappform->posts="";
	
	//db function variables
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpfb_gettwitter_forms';
	
	//form deleting and updating here---------------------------
	if(isset($_GET['taction'])){
		if(isset($_GET['tid'])){
			$tid = htmlentities($_GET['tid']);
			$tid = intval($tid);
			//for deleting
			if($_GET['taction'] == "del" && $_GET['tid'] > 0){
				//security
				check_admin_referer( 'tdel_');
				//delete
				$wpdb->delete( $table_name, array( 'id' => $tid ), array( '%d' ) );
			}
			//for updating
			if($_GET['taction'] == "edit" && $_GET['tid'] > 0){
				//security
				check_admin_referer( 'tedit_');
				//get form array
				$currentgetappform = $wpdb->get_row( "SELECT * FROM ".$table_name." WHERE id = ".$tid );
			}
			//for copying
			if($_GET['taction'] == "copy" && $_GET['tid'] > 0){
				//security
				check_admin_referer( 'tcopy_');
				//get form array
				$currentgetappform = $wpdb->get_row( "SELECT * FROM ".$table_name." WHERE id = ".$tid );
				//add new template
				$array = (array) $currentgetappform;
				$array['title'] = $array['title'].'_copy';
				
				unset($array['id']);
				//print_r($array);
				//remove the id so it can be generated.
				$wpdb->insert( $table_name, $array );
				//$wpdb->show_errors();
				//$wpdb->print_error();
			}
		}
		
	}
	//------------------------------------------
	

	//form posting here--------------------------------
	//for saving X (Twitter) API credentials (OAuth 1.0a keys and/or Bearer Token)
	$keystatus['ack'] = '';
	$keystatus['msg'] ='';
	$keystatus['html'] ='';
	if (isset($_POST['wprevpro_savecookie'])){
		//verify nonce wp_nonce_field( 'wprevpro_save_template');
		check_admin_referer( 'wprevpro_save_cookie');

		$wprevpro_twitter_api_key = trim(sanitize_text_field($_POST['wprevpro_twitter_api_key']));
		$wprevpro_twitter_api_key_secret = trim(sanitize_text_field($_POST['wprevpro_twitter_api_key_secret']));
		$wprevpro_twitter_api_token = trim(sanitize_text_field($_POST['wprevpro_twitter_api_token']));
		$wprevpro_twitter_api_token_secret = trim(sanitize_text_field($_POST['wprevpro_twitter_api_token_secret']));
		update_option( 'wprevfb_twitterapi_key', $wprevpro_twitter_api_key );
		update_option( 'wprevfb_twitterapi_key_secret', $wprevpro_twitter_api_key_secret );
		update_option( 'wprevfb_twitterapi_token', $wprevpro_twitter_api_token );
		update_option( 'wprevfb_twitterapi_token_secret', $wprevpro_twitter_api_token_secret );

		$wprevpro_twitter_bearer = trim(sanitize_text_field($_POST['wprevpro_twitter_bearer']));
		//Users sometimes paste the header value including the "Bearer " prefix.
		if(stripos($wprevpro_twitter_bearer, 'Bearer ') === 0){
			$wprevpro_twitter_bearer = trim(substr($wprevpro_twitter_bearer, 7));
		}
		update_option( 'wprevfb_twitterapi_bearer', $wprevpro_twitter_bearer );

		$justsavedkeys = true;
	} else {
		$justsavedkeys = false;
	}

	//load saved credentials.
	$wprevpro_twitter_api_key = get_option('wprevfb_twitterapi_key');
	$wprevpro_twitter_api_key_secret = get_option('wprevfb_twitterapi_key_secret');
	$wprevpro_twitter_api_token = get_option('wprevfb_twitterapi_token');
	$wprevpro_twitter_api_token_secret = get_option('wprevfb_twitterapi_token_secret');
	$wprevpro_twitter_bearer = get_option('wprevfb_twitterapi_bearer');

	$has_oauth1 = ($wprevpro_twitter_api_key!='' && $wprevpro_twitter_api_key_secret!='' && $wprevpro_twitter_api_token!='' && $wprevpro_twitter_api_token_secret!='');
	$has_bearer = ($wprevpro_twitter_bearer!='');
	//credentials are validated on the first search (avoids burning paid read credits on a separate check).
	$keysinput = ($has_oauth1 || $has_bearer);

	//show or hide the key form: show it (with a confirmation) right after saving,
	//show it when no credentials are saved yet, otherwise keep it hidden.
	$keystatushtml = '';
	if($justsavedkeys){
		$keyformhideshow = '';
		if($has_oauth1 || $has_bearer){
			$keystatus['ack'] = 'success';
			$savedparts = array();
			if($has_oauth1){
				$savedparts[] = 'OAuth 1.0a keys';
			}
			if($has_bearer){
				$savedparts[] = 'Bearer Token';
			}
			$keystatushtml = '<div style="color:green;">Saved: '.esc_html(implode(' and ', $savedparts)).'. Credentials will be checked the first time you search for posts.</div>';
		} else {
			$keystatushtml = '<div style="color:red;">Enter either all four OAuth 1.0a keys, or a Bearer Token (or both).</div>';
		}
	} else if(!$keysinput){
		$keyformhideshow = '';	//no credentials yet, show the form
	} else {
		$keyformhideshow = 'wprevpro_hide';	//credentials saved, hide by default
	}
	
	
	

	//check to see if form has been posted.
	//if template id present then update database if not then insert as new.

	if (isset($_POST['wprevpro_submittemplatebtn'])){
		//verify nonce wp_nonce_field( 'wprevpro_save_template');
		check_admin_referer( 'wprevpro_save_template');
		//get form submission values and then save or update
		$t_id = sanitize_text_field($_POST['edittid']);
		$title = sanitize_text_field($_POST['wprevpro_template_title']);
		$site_type = "Twitter";
		$query = sanitize_text_field($_POST['wprevpro_query']);
		
		$endpoint = sanitize_text_field($_POST['wprevpro_endpoint']);

		//$cron = sanitize_text_field($_POST['wprevpro_cron_setting']);
		//$blocks = sanitize_text_field($_POST['wprevpro_blocks']);
		//$blocks = intval($blocks);
		$blocks=100;
		
		//$last_name = sanitize_text_field($_POST['wprevpro_last_name']);
		$profile_img = sanitize_text_field($_POST['wprevpro_profile_img']);

		$timenow = time();
		
		
		//+++++++++need to sql escape using prepare+++++++++++++++++++
		//+++++++++++++++++++++++++++++++++++++++++++++++++++++
		//insert or update
			$data = array( 
				'title' => "$title",
				'site_type' => "$site_type",
				'created_time_stamp' => "$timenow",
				'query' => "$query",
				'endpoint' => "$endpoint",
				'blocks' => "$blocks",
				'profile_img' => "$profile_img",
				'categories' => "",
				'posts' => "",
				);
				//print_r($data);
			$format = array( 
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				); 

		if($t_id==""){
			//print_r($data);
			//insert
			$insertrow = $wpdb->insert( $table_name, $data, $format );
			if(!$insertrow){
			//$wpdb->show_errors();
			//$wpdb->print_error();
			$dbmsg = $dbmsg.'<div id="setting-error-wprevpro_message" class="error settings-error notice is-dismissible">'.__('<p><strong>Oops! This form could not be inserted in to the database.</br> -'.$wpdb->show_errors().' -'.$wpdb->print_error().' </strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-fb-reviews').'</div>';
			}
			//die();
		} else {
			//update
			//print_r($data);
			$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $t_id ), $format, array( '%d' ));
			//$wpdb->show_errors();
			//$wpdb->print_error();
			if($updatetempquery>0){
				$dbmsg = $dbmsg.'<div id="setting-error-wprevpro_message" class="updated settings-error notice is-dismissible">'.__('<p><strong>Form Updated!</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-fb-reviews').'</div>';
			} else {
				$dbmsg = $dbmsg.'<div id="setting-error-wprevpro_message" class="error settings-error notice is-dismissible">'.__('<p><strong>Oops! The Form could not be updated in the database. </strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>', 'wp-fb-reviews').'</div>';
			}
		}
		
		
	}

	//Get list of all current forms--------------------------
	$currentforms = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
	//-------------------------------------------------------

	
?>

<div class="">
<h1></h1>
<div class="wrap" id="wp_rev_maindiv">

	<img class="wprev_headerimg" src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png?id='.$this->_token; ?>">
	
<?php 
include("tabmenu.php");

//query args for export and import
$url_tempdownload = admin_url( 'admin-post.php?action=print_reviewfunnel.csv' );

	
?>
<div class="welcomecontainer wpfbr_margin10 w3-row-padding w3-section w3-stretch">

<div class="w3-col w3-container ">
<div class="welcomediv w3-white w3-border w3-border-light-gray2 w3-round-small">

<div class="w3-col wpfbr_margin10">
<div class="wprevpro_margin10">
	<a id="wprevpro_addnewtemplate" keycheck="<?php echo $keystatus['ack']; ?>" class="button dashicons-before dashicons-plus-alt"><?php _e('Add New X (Twitter) Source', 'wp-fb-reviews'); ?></a>
	<a id="wprevpro_addnewapikey" class="button dashicons-before dashicons-plus-alt"><?php _e('Enter/Modify API Keys', 'wp-fb-reviews'); ?></a>

</div>
<div class='bordereddiv'><?php _e('Use this page to search for and download posts about your business/product/service from X (formerly Twitter). Click the <b>"Add New X (Twitter) Source"</b> button to get started. Enter your X Developer credentials with <b>"Enter/Modify API Keys"</b> before searching. Prefer OAuth 1.0a (Consumer Key, Consumer Key Secret, Access Token, Access Token Secret); a Bearer Token can be used as a fallback.', 'wp-fb-reviews'); ?> </div>

<div id="apikeyformdiv" class="<?php echo $keyformhideshow; ?> wprevpro_margin10 bordered_form" id="login_cookie">
	    <form  action="?page=wpfb-get_twitter" method="post" name="logincookie" enctype="multipart/form-data">
		<table class="">
		<tbody>
			<tr class="wprevpro_row">
				<td scope="row" style="width:50%;">
				<div class="twitter_key_header"><?php _e('OAuth 1.0a — Consumer Key & Secret (recommended):', 'wp-fb-reviews'); ?></div>
				<div class="twitter_key_div"><?php _e('Consumer Key:', 'wp-fb-reviews'); ?> <input class="inputrow100per" type="text" name="wprevpro_twitter_api_key" id="wprevpro_twitter_api_key" value="<?php echo esc_attr(get_option('wprevfb_twitterapi_key')); ?>"></div>
				<div class="twitter_key_div"><?php _e('Consumer Key Secret:', 'wp-fb-reviews'); ?> <input class="inputrow100per" type="text" name="wprevpro_twitter_api_key_secret" id="wprevpro_twitter_api_key_secret" value="<?php echo esc_attr(get_option('wprevfb_twitterapi_key_secret')); ?>"></div>
				</td>
				<td scope="row" style="padding-left:10px;">
				<div class="twitter_key_header"><?php _e('OAuth 1.0a — Access Token & Secret:', 'wp-fb-reviews'); ?></div>
				<div class="twitter_key_div"><?php _e('Access Token:', 'wp-fb-reviews'); ?> <input class="inputrow100per" type="text" name="wprevpro_twitter_api_token" id="wprevpro_twitter_api_token" value="<?php echo esc_attr(get_option('wprevfb_twitterapi_token')); ?>"></div>
				<div class="twitter_key_div"><?php _e('Access Token Secret:', 'wp-fb-reviews'); ?> <input class="inputrow100per" type="text" name="wprevpro_twitter_api_token_secret" id="wprevpro_twitter_api_token_secret" value="<?php echo esc_attr(get_option('wprevfb_twitterapi_token_secret')); ?>"></div>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<td scope="row" colspan="2">
				<div class="twitter_key_header"><?php _e('Bearer Token (optional fallback):', 'wp-fb-reviews'); ?></div>
				<div class="twitter_key_div"><input class="inputrow100per" type="text" name="wprevpro_twitter_bearer" id="wprevpro_twitter_bearer" value="<?php echo esc_attr(get_option('wprevfb_twitterapi_bearer')); ?>"></div>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<td scope="row" colspan="2">
				<p class="description">
				<?php _e('Create an App inside a Project at <a href="https://developer.x.com/" target="_blank">developer.x.com</a> / <a href="https://console.x.com/" target="_blank">console.x.com</a>. Under <b>OAuth 1.0a Keys</b>, copy the <b>Consumer Key</b>, <b>Consumer Key Secret</b>, then generate and copy the <b>Access Token</b> and <b>Access Token Secret</b> (preferred). You may also paste the <b>Bearer Token</b> as a fallback. Search uses OAuth 1.0a when all four keys are present; otherwise it uses the Bearer Token. Paid X API access (Pay Per Use or equivalent) is required for Recent Search. Instructions: <a href="https://wpreviewslider.com/how-to-get-your-x-twitter-api-keys-and-access-tokens/" target="_blank">this page</a>.', 'wp-fb-reviews'); ?></p>
				</td>
			</tr>
			
			</tbody>
			</table>
				<?php 
				//security nonce
				wp_nonce_field( 'wprevpro_save_cookie');
				?>
			<input type="submit" name="wprevpro_savecookie" id="wprevpro_savecookie" class="button button-primary" value="<?php _e('Save', 'wp-fb-reviews'); ?>">
&nbsp;&nbsp;<a href="https://wpreviewslider.com/how-to-get-your-x-twitter-api-keys-and-access-tokens/" target="_blank" id="instr" name="instr" class="button-secondary "><?php _e('API Instructions', 'wp-fb-reviews'); ?></a>
<?php echo $keystatushtml; ?>
        </form>
</div>


  <div class="wprevpro_margin10" id="wprevpro_new_template">
<form name="newtemplateform" id="newtemplateform" action="?page=wpfb-get_twitter" method="post">
	<table class="wprevpro_margin10 form-table ">
		<tbody>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Form Title:', 'wp-fb-reviews'); ?>
				</th>
				<td>
					<input id="wprevpro_template_title" data-custom="custom" type="text" name="wprevpro_template_title" placeholder="" value="<?php echo esc_html($currentgetappform->title); ?>" required>
					<p class="description">
					<?php _e('Enter a unique name for these tweets. This would normally be the name of business/product/service the tweets are talking about.', 'wp-fb-reviews'); ?>		</p>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Search Terms (query):', 'wp-fb-reviews'); ?>
				</th>
				<td>
					<input class="yelp_business_url" id="wprevpro_query" data-custom="custom" type="text" name="wprevpro_query" placeholder="" value='<?php echo esc_html($currentgetappform->query); ?>' required>
					<p class="description">
					<?php _e('X search query rules apply. Spaces mean <b>AND</b> (all words must appear in the same post). Use <b>OR</b> to match either term, and quotes for an exact phrase. Recent search only covers the <b>last 7 days</b>. Operators: <a href="https://developer.x.com/en/docs/x-api/tweets/search/integrate/build-a-query" target="_blank">build a query</a>.', 'wp-fb-reviews'); ?>		</p>
					<p class="description">
					<?php _e('Examples: <code>Yellowhammer</code> &nbsp;|&nbsp; <code>Yellowhammer OR Huntsville</code> &nbsp;|&nbsp; <code>"Yellowhammer Brewing"</code> &nbsp;|&nbsp; <code>Yellowhammer -is:retweet</code>', 'wp-fb-reviews'); ?></p>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Search API:', 'wp-fb-reviews'); ?>
				</th>
				<td>
					<select name="wprevpro_endpoint" id="wprevpro_endpoint">
					  <option value="7" <?php if($currentgetappform->endpoint=='7' || $currentgetappform->endpoint=='30' || $currentgetappform->endpoint==''){echo "selected";} ?>>Recent search (last 7 days)</option>
					  <option value="all" <?php if($currentgetappform->endpoint=='all'){echo "selected";} ?>>Full-archive search</option>
					</select>
					<p class="description">
					<?php _e('This plugin uses the X API v2 to look for posts. <b>Recent search</b> returns posts from the last 7 days and works with standard developer access. <b>Full-archive search</b> can return older posts but requires elevated/paid access to the <code>tweets/search/all</code> endpoint on your X Developer account. Either option requires your own API Keys entered above.', 'wp-fb-reviews'); ?>		</p>
				</td>
			</tr>
			<tr class="wprevpro_row">
				<th scope="row">
					<?php _e('Local Images', 'wp-fb-reviews'); ?>
				</th>
				<td>
					<input type="radio" name="wprevpro_profile_img" value="no" <?php if($currentgetappform->profile_img=='no' || $currentgetappform->profile_img==''){echo "checked";} ?>><?php _e('No', 'wp-fb-reviews'); ?>&nbsp;&nbsp;&nbsp;
					<input type="radio" name="wprevpro_profile_img" value="yes" <?php if($currentgetappform->profile_img=='yes' ){echo "checked";} ?>><?php _e('Yes', 'wp-fb-reviews'); ?>&nbsp;&nbsp;&nbsp;
					<p class="description">
					<?php _e('By default, images are referenced from the original source server. Set this to yes if you would like the plugin to try and save the images locally. This may not always work as the remote site might block the download.', 'wp-fb-reviews'); ?></p>
				</td>
			</tr>


		</tbody>
	</table>
	<?php 
	//security nonce
	wp_nonce_field( 'wprevpro_save_template');
	?>
	<input type="hidden" name="edittid" id="edittid"  value="<?php echo esc_html($currentgetappform->id); ?>">
	<input type="submit" name="wprevpro_submittemplatebtn" id="wprevpro_submittemplatebtn" class="button button-primary" value="<?php _e('Save', 'wp-fb-reviews'); ?>">
	<a id="wprevpro_addnewtemplate_cancel" class="button button-secondary"><?php _e('Cancel', 'wp-fb-reviews'); ?></a>
	</form>
</div>
  

<?php

//display message
echo $dbmsg;
		$html .= '
		<table class="wp-list-table widefat striped posts">
			<thead>
				<tr>
					<th scope="col" width="40px" class="manage-column">'.__('ID', 'wp-fb-reviews').'</th>
					<th scope="col" class="manage-column">'.__('Title', 'wp-fb-reviews').'</th>
					<th scope="col" class="manage-column">'.__('Query', 'wp-fb-reviews').'</th>
					<th scope="col" width="115px" class="manage-column">'.__('Last Checked', 'wp-fb-reviews').'</th>
					<th scope="col" width="390px" class="manage-column">'.__('Action', 'wp-fb-reviews').'</th>
				</tr>
				</thead>
			<tbody id="appformstable">';
	if(count($currentforms)>0){
	foreach ( $currentforms as $currentform ) 
	{
	//remove query args we just used
	$urltrimmed = remove_query_arg( array('taction', 'id') );
		$tempeditbtn =  add_query_arg(  array(
			'taction' => 'edit',
			'tid' => "$currentform->id",
			),$urltrimmed);
			
		$url_tempeditbtn = wp_nonce_url( $tempeditbtn, 'tedit_');
			
		$tempdelbtn = add_query_arg(  array(
			'taction' => 'del',
			'tid' => "$currentform->id",
			),$urltrimmed) ;
			
		$url_tempdelbtn = wp_nonce_url( $tempdelbtn, 'tdel_');
		
						//for copying
		$tempcopybtn = add_query_arg(  array(
			'taction' => 'copy',
			'tid' => "$currentform->id",
			),$urltrimmed) ;
		$url_tempcopybtn = wp_nonce_url( $tempcopybtn, 'tcopy_');
			
		$lastranon = '';
		if($currentform->last_ran>0){$lastranon = date("M j, Y",$currentform->last_ran);}
		
		//$fposts = addslashes($currentform->posts);
		$fposts = str_replace('"',"'",$currentform->posts);
		//$fcategories = addslashes($currentform->categories);
		$fcategories = str_replace('"',"'",$currentform->categories);
			
		$html .= '<tr id="'.$currentform->id.'">
				<th scope="col" class=" manage-column">'.esc_html($currentform->id).'</th>
				<th scope="col" class=" manage-column" style="min-width: 200px;"><b><span class="titlespan">'.esc_html($currentform->title).'</span></b></th>
				<th scope="col" class="tdquery manage-column">'.esc_html($currentform->query).'</th>
				<th scope="col" class=" manage-column">'.esc_html($lastranon).'</th>
				<th scope="col" class="manage-column" limage="'.esc_url($currentform->profile_img).'" fcats="'.esc_attr($fcategories).'" fposts="'.esc_attr($fposts).'" ftitle="'.esc_attr($currentform->title).'" epoint="'.esc_attr($currentform->endpoint).'" squery="'.esc_attr($currentform->query).'"><a href="'.$url_tempeditbtn.'" class="rfbtn button button-secondary dashicons-before dashicons-admin-generic">'.__('Edit', 'wp-fb-reviews').'</a> <a href="'.$url_tempdelbtn.'" class="rfbtn button button-secondary dashicons-before dashicons-trash">'.__('Delete', 'wp-fb-reviews').'</a> <a href="'.$url_tempcopybtn.'" class="rfbtn button button-secondary dashicons-before dashicons-admin-page">'.__('Copy', 'wp-fb-reviews').'</a> <span class="rfbtn button button-primary dashicons-before dashicons-star-filled retreviewsbtn"> '.__('Get Posts', 'wp-fb-reviews').'</span></th>
			</tr>';
	}
	} else {
		$html .= '<tr><td colspan="5">'.__('You can create a Review Form to download posts from X (formerly Twitter)! Once downloaded, they will show up on the Review List page of the plugin and you can display them on your website with a Review Template. Click the "Add New X (Twitter) Source" button above to get started.', 'wp-fb-reviews').'</td></tr>';
	}
		$html .= '</tbody></table>';
echo $html;
//echo "<div></br>Coming Soon! You will be able to easily search and download twitter posts!</br></br></div>"; 

?>

<div id="retreivewspopupdiv" style="display:none;">
	<div id="tb_content_query">
	<input id="tb_content_query_input" data-custom="custom" type="text" name="tb_content_query_input" value="">&nbsp;<span class="button button-secondary updatequery"><?php _e('Update', 'wp-fb-reviews'); ?></span>
	</div>
	<div class="downloadrevsbtnspinner"></div>
	<table id="selecttweets" class="wp-list-table widefat striped posts">
	</table>
	<div class="ajaxmessagediv"></div>
	
</div>
					

	<div id="popup_review_list" class="popup-wrapper wprevpro_hide">
	  <div class="popup-content">
		<div class="popup-title">
		  <button type="button" class="popup-close">&times;</button>
		  <h3 id="popup_titletext"></h3>
		</div>
		<div class="popup-body">
		  <div id="popup_bobytext1"></div>
		  <div id="popup_bobytext2"></div>
		</div>
	  </div>
	</div>
	

</div>
<?php
//echo "<br><br><br>";
//print_r($licensecheckarray);
?>
</div>
</div>
</div>
</div>
</div>
