<?php

/******************************************************************************************************************
 
Plugin Name: Theme Info

Plugin URI:

Description: WordPress plugin for letting site admins easily see what themes are actively used on their site

Version: 1.5

Author: Kevin Graeme, Deanna Schneider & Jason Lemahieu


Copyright:

    Copyright 2009 Board of Regents of the University of Wisconsin System
	Cooperative Extension Technology Services
	University of Wisconsin-Extension

            
*******************************************************************************************************************/

class cets_Theme_Info {


function cets_theme_info() {
	global $wp_version;
	
	if ( version_compare( $wp_version, '3.0', '>=' ) ) {
		// Add the site admin config page
		
		if (function_exists('is_network_admin')) {
			//3.1+
			add_action('network_admin_menu', array(&$this, 'theme_info_add_page'));
		}
		else {
			//-3.1
			add_action('admin_menu', array(&$this, 'theme_info_add_page'));
		}
		
		add_filter('theme_action_links', array(&$this, 'action_links'), 9, 3);
		add_action('switch_theme', array(&$this, 'on_switch_theme'));
		
		if ( in_array( basename($_SERVER['PHP_SELF']), array('themes.php') ))  {
				
				add_action('admin_enqueue_scripts', array(&$this, 'cets_theme_info_admin_scripts_theme_page'));
				
				// run the function to generate the theme blog list (this runs whenever the theme page reloads, but only regenerates the list if it's more than an hour old or not set yet)
				$gen_time = get_site_option('cets_theme_info_data_freshness');
	
		
				if ((time() - $gen_time) > 3600 || strlen($gen_time) == 0) {
					$this->generate_theme_blog_list();
				}	
					
			}
		
	}
}  // function cets_theme_info

// added by Jason to properly enqueue scripts at right time
function  cets_theme_info_admin_scripts_theme_page() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('thickbox');
}

function generate_theme_blog_list() {
	global $wpdb, $current_site;
		
		//require_once('admin.php');
		$blogs  = $wpdb->get_results("SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = {$current_site->id} ORDER BY domain ASC");
		$blogthemes = array();
		$processedthemes = array();
		if ($blogs) {
		foreach ($blogs as $blog) {
			switch_to_blog($blog->blog_id);
			$ct = get_current_theme();
			
			if( constant( 'VHOST' ) == 'yes' ) {
				$blogurl = $blog->domain;
			} else {
				$blogurl =  trailingslashit( $blog->domain . $blog->path );
			}
						
			if (array_key_exists($ct, $processedthemes) == false) {
				//echo ("<p> Blogid = " .  $blog->blog_id . " & current theme = " . $ct . "</p>");
				$blogthemes[$ct][0] = array('blogid' => $blog->blog_id, /*'path' => $path, 'domain' => $domain,*/ 'name' => get_bloginfo('name'), 'blogurl' => $blogurl);	
				$processedthemes[$ct] = true;
				
			}
			else {
				//get the size of the current array of blogs
				$count = sizeof($blogthemes[$ct]);
				//echo ("<p> Blogid = " .  $blog->blog_id . " & current theme = " . $ct . "</p>");
				$blogthemes["$ct"][$count] = array('blogid' => $blog->blog_id,/* 'path' => $path, 'domain' => $domain,*/ 'name' => get_bloginfo('name'), 'blogurl' => $blogurl);
				
			}			
			
			restore_current_blog();
			}
		}
	// Set the site option to hold all this
	update_site_option('cets_theme_info_data', $blogthemes);
	
	update_site_option('cets_theme_info_data_freshness', time());
	
	
}

//apply_filters( 'theme_action_links', array_filter( $actions ), $theme_key, $theme, $context ); 
function action_links($actions, $theme_key, $theme=''){

	// Get the toggle to see if users can view this information
	$allow = get_site_option('cets_theme_info_allow');
	
	// if it's not the site admin and users aren't allowed to be in here, just get out.
	if ($allow != 1 && !is_super_admin()) {
		return $actions;
	}
		
	if (is_array($theme_key)) {
		//theme choosing page
		$actual_theme_key = $theme_key['Template'];
		$theme = $theme_key;
		$theme_key = $actual_theme_key;
	}
		
	//get the list of blogs for this theme
	$data = get_site_option('cets_theme_info_data');
		
	if (isset($data[$theme['Name']])) {
		$blogs = $data[$theme['Name']];
	} else {
		$blogs = array();
	}
	
	// get the first param of the actions var and add some more stuff before it	
	//$start = $actions[0];
	$name = str_replace(" ", "_", $theme['Name']);
	$text = "<div class='cets_theme_info'>Used on ";
	 if (sizeOf($blogs) > 0) {
	 	$text .=' <a href="#TB_inline?height=155&width=300&inlineId='. $name . '" class="thickbox" title="Blogs that use this theme">';
	 }
	$text .= sizeOf($blogs) . " blog";
	if (sizeOf($blogs) != 1) {$text .= 's';}
	if (sizeOf($blogs)> 0 ) {
		$text .= '</a>';
	}
	$text .=('. ');
	if(sizeOf($blogs) > 0){
		
		$text .= '<div id="' . $name . '" style="display: none"><div>';
		
		// loop through the list of blogs and display their titles
		$text .=("Activated on the following blogs: <ul>");
		foreach ($blogs as $blog){
			$text .= '<li><a href="http://' . $blog['blogurl'] . '" target="new">' . $blog['name'] . '</a></li>';
			
			
		}
		$text .= "</li>";
		$text .= "</div></div>";
		
	} 
	$text .='</div>';
	
	//$text .= $start;
	//$actions[0] = $text;
	
	array_push($actions, $text);
	
	return $actions;
	
	}
	

// Create a function to add a menu item for site admins
function theme_info_add_page() {
	// Add a submenu
	if(is_super_admin()) {
		
		
		if (function_exists('is_network_admin')) {
			$page=	add_submenu_page('themes.php', 'Theme Usage Info', 'Theme Usage Info', 'manage_network', basename(__FILE__), array(&$this, 'theme_info_page'));
		}
		else{
			$page=	add_submenu_page('wpmu-admin.php', 'Theme Usage Info', 'Theme Usage Info', 'manage_network', basename(__FILE__), array(&$this, 'theme_info_page'));
		}
	}
	
	
	

}




// Create a function to actually display stuff on theme usage
function theme_info_page(){
	
	//Handle updates
    	if (isset($_POST['action']) && $_POST['action'] == 'update') {
			update_site_option('cets_theme_info_allow', $_POST['usage_flag']);
		?>
        	<div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div>
   <?php
    	}
		
	// get the usage setting
	$usage_flag = get_site_option('cets_theme_info_allow');
	
	// if it's not set, set it to zero (the default)
	if (strlen($usage_flag) == 0) {
		$usage_flag = 0;
		update_site_option('cets_theme_info_allow', 0);
	}
	
	
	// Get the time when the theme list was last generated
	$gen_time = get_site_option('cets_theme_info_data_freshness');
	
	if ((time() - $gen_time) > 3600) {
		// if older than an hour, regenerate, just to be safe
			$this->generate_theme_blog_list();	
	}
	$allowed_themes = get_site_allowed_themes();
	$themes = get_themes();
	$list = get_site_option('cets_theme_info_data');
	ksort($list);

	
	// figure out what themes have not been used at all
	$unused_themes = array();
	foreach($themes as $theme){
		if (!array_key_exists($theme['Name'], $list)){
			array_push($unused_themes, $theme);
			
		}
		
	}
	$file = WP_CONTENT_URL . '/mu-plugins/cets_theme_info/lib/tablesort.js';
	?>
	<!-- This pulls in the table sorting script -->
	<SCRIPT LANGUAGE='JavaScript1.2' SRC='<?php echo $file; ?>'></SCRIPT>
	<!-- Some extra CSS -->
	<style type="text/css">
		.bloglist {
			display:none;
		}
		.pc_settings_heading {
			text-align: center; 
			border-right:  3px solid black;
			border-left: 3px solid black;
			
		}
		.pc_settings_left {
			border-left: 3px solid black;
		}
		.pc_settings_right {
			border-right: 3px solid black;
		}
	</style>
	<div class="wrap">
		<h2>Theme Usage Information</h2>
		<table class="widefat" id="cets_active_themes">
			
			<thead>
				<tr>
					<th style="width: 25%;" class="nocase">Used Theme</th>
					<th style="width: 25%;" class="case">Activated Sitewide</th>
					<th style="width: 25%;" class="num">Total Blogs</th>
					<th style="width: 25%;">Blog Titles</th>

				</tr>
			</thead>
			<tbody id="plugins">
	<?php
	$counter = 0;
	foreach ($list as $theme => $blogs){
		$counter = $counter + 1;
		echo('<tr valign="top"><td>' .$theme .'</td><td>');
		
		// get the array for this theme
		$thisTheme = $themes[$theme];
		if (array_key_exists($thisTheme['Stylesheet'], $allowed_themes)) { echo ("Yes");}
		else {echo ("No");}
		echo ('</td><td>' . sizeOf($blogs) . '</td><td>');
		
		?>
		<a href="javascript:void(0)" onClick="jQuery('#bloglist_<?php echo $counter; ?>').toggle(400);">Show/Hide Blogs</a>
		
		
		<?php
		echo ('<ul class="bloglist" id="bloglist_' . $counter  . '">');
		foreach($blogs as $key => $row){
				$name[$key] = $row['name'];
				$blogurl[$key] = $row['blogurl'];
			}
			if (sizeOf($name) == sizeOf($blogs)){
				array_multisort($name, SORT_ASC, $blogs);
			}
			
			foreach($blogs as $blog){
				echo ('<li><a href="http://' . $blog['blogurl'] . '" target="new">' . $blog['name'] . '</a></li>');
			}
			
		echo ('</ul></td>');
		
		
		
		
		
	}
	?>
		</tbody>
		</table>
		<p>&nbsp;</p>
		<table class="widefat">
			<thead>
				<tr>
					<th style="width: 25%;" class="nocase">Unused Theme</th>
					<th style="width: 25%;" class="case">Activated Sitewide</th>
					<th style="width: 25%;">Total Blogs</th>
					<th style="width: 25%;">&nbsp;</th>
				</tr>
			</thead>
			<tbody id="plugins">
			<?php
			asort($unused_themes);
			foreach($unused_themes as $theme) {
				echo ("<tr><td>" . $theme['Name'] . "</td><td>");
				if (array_key_exists($theme['Stylesheet'], $allowed_themes)) { echo ("Yes");}
				else {echo ("No");}
				echo("</td><td>0</td><td>&nbsp;</td></tr>");
			}
			?>	
			</tbody>
			</table>
	
	<h2>Manage User Access</h2>
	<p>Users can see usage information for themes in Appearance -> Themes. You can control user access to that information via this toggle.</p>
	<form name="themeinfoform" action="" method="post">
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">Let Users View Theme Usage Information: </th>
					<td><label><input type="radio" name="usage_flag" value="1" <?php checked('1', $usage_flag) ?> /> Yes</label><br/>
					<label><input type="radio" name="usage_flag" value="0" <?php checked('0', $usage_flag) ?> /> No</label>
					</td>
						
				</tr>
		    </tbody>
		</table>		
		
	<p>
	<input type="hidden" name="action" value="update" />
    <input type="submit" name="Submit" value="Save Changes" />
	</p>
	</form>
	</div>
	<?php
	

	
	
		
}

function on_switch_theme() {
	$this->generate_theme_blog_list();
	
}


}// end class


add_action( 'plugins_loaded', create_function( '', 'global $cets_Theme_Info; $cets_Theme_Info = new cets_Theme_Info();' ) );



?>