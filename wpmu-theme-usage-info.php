<?php
/**
 * @author    Christian Foellmann & Jason Lemahieu and Kevin Graeme (Cooperative Extension Technology Services)
 * @copyright Copyright (c) 2009 - 2014, Cooperative Extension Technology Services
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package   WP-Repository\WPMU_Theme_Usage_Info
 * @version   2.0.0
 */
/*
Plugin Name: WPMU Theme Usage Info
Plugin URI: https://wordpress.org/plugins/wpmu-theme-usage-info/
Description: WordPress plugin for letting network admins easily see what themes are actively used on the network
Version: 2.0.0
Author: Christian Foellmann & Jason Lemahieu
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpmu-theme-usage-info
Domain Path: /languages
Network: true

	WPMU Theme Usage Info

	Copyright (C) 2014 Christian Foellmann (http://christian.foellmann.de)
	Copyright (C) 2009 - 2013 Board of Regents of the University of Wisconsin System
	Cooperative Extension Technology Services
	University of Wisconsin-Extension

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Main class to run the plugin
 *
 * @since 1.0.0
 */
class WPMU_Theme_Usage_Info {

	/**
	 * Current version of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string $version
	 */
	public $version = '2.0.0';

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		/* Do nothing here */
	} // END __construct()

	/**
	 * Hook in actions and filters
	 *
	 * @since 2.0.0
	 */
	private function setup_actions() {

		/** Actions ***********************************************************/
		add_action( 'switch_theme',       array( $this, 'switch_theme' ) );
		add_action( 'plugins_loaded',     array( $this, 'load_plugin_textdomain' ) );
		add_action( 'network_admin_menu', array( $this, 'network_admin_menu' ) );
		add_action( 'load-themes_page_wpmu-theme-usage-info', array( $this, 'load_admin_assets' ) );

		/** Filters ***********************************************************/
		add_filter( 'plugin_row_meta',    array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'theme_action_links', array( $this, 'action_links' ),    9,  3 );

		register_activation_hook( __FILE__, array( 'WPMU_Theme_Usage_Info', 'activation' ) );

	} // END setup_actions()

	/**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 2.0.0
	 *
	 * @return WPMU_Theme_Usage_Info|null The instance object
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new WPMU_Theme_Usage_Info;
			$instance->setup_actions();
		}

		// Always return the instance
		return $instance;

	} // END instance()

	function OLD__construct() {

		if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'themes.php' ) ) )  {
			// run the function to generate the theme blog list (this runs whenever the theme page reloads, but only regenerates the list if it's more than an hour old or not set yet)
			$gen_time = get_site_option( 'cets_theme_info_data_freshness' );

			if ( ( time() - $gen_time ) > 3600 || strlen( $gen_time ) == 0 ) {
				$this->generate_theme_blog_list();
			}
		}
		
	} // END __construct()
	
	/**
	 * Fetch sites and the active plugins every single site
	 *
	 * @since 1.0.0
	 *
	 * @see get_site_option()
	 * @see add_site_option()
	 * @see update_site_option( )
	 */
	private function maybe_update() {

		$this_version = $this->version;
		$plugin_version = get_site_option( 'cets_theme_info_version', 0 );

		if ( version_compare( $plugin_version, $this_version, '<' ) ) {
			add_site_option( 'cets_theme_info_data_freshness', 1 );
			update_site_option( 'cets_theme_info_data_freshness', 2 );
		}

		if ( $plugin_version == 0 ) {
			add_site_option( 'cets_theme_info_version', $this_version );
		} else {
			update_site_option( 'cets_theme_info_version', $this_version );
		}
	}
	
	/**
	 * Fetch sites and the active themes for every single site
	 *
	 * @since 1.0.0
	 *
	 * @see switch_to_blog()
	 * @see wp_get_theme()
	 * @see trailingslashit()
	 * @see get_bloginfo()
	 * @see restore_current_blog()
	 * @see update_site_option()
	 *
	 * @global object $wpdb
	 * @global array $current_site
	 * @global string $wp_version
	 */
	private function generate_theme_blog_list() {
//		@TODO fetch all themes and list them with number of blogs even if count == 0
		global $wpdb, $current_site, $wp_version;

		$blogs  = $wpdb->get_results( "SELECT blog_id, domain, path FROM " . $wpdb->blogs . " WHERE site_id = {$current_site->id} ORDER BY domain ASC" );
		$blogthemes = array();
		$processedthemes = array();
		
		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog->blog_id );
				$cto = wp_get_theme();
				$ct = $cto->stylesheet;

				if ( constant( 'VHOST' ) == 'yes' ) {
					$blogurl = $blog->domain;
				} else {
					$blogurl = trailingslashit( $blog->domain . $blog->path );
				}

				if ( array_key_exists( $ct, $processedthemes ) == false ) {
					$blogthemes[ $ct ][0] = array(
						'blogid' => $blog->blog_id,
						/*'path' => $path, 'domain' => $domain,*/
						'name' => get_bloginfo( 'name' ),
						'blogurl' => $blogurl,
					);	
					$processedthemes[ $ct ] = true;
				} else {
					//get the size of the current array of blogs
					$count = sizeof( $blogthemes[ $ct ] );
					$blogthemes["$ct"][ $count ] = array(
						'blogid' => $blog->blog_id,
						/* 'path' => $path, 'domain' => $domain,*/
						'name' => get_bloginfo( 'name' ),
						'blogurl' => $blogurl,
					);
				}
				
				restore_current_blog();
				
			}
		}
		
		// Set the site option to hold all this
		update_site_option( 'cets_theme_info_data', $blogthemes );
		update_site_option( 'cets_theme_info_data_freshness', time() );
		
	} // END generate_theme_blog_list()
	
	/**
	 * Fetch sites and the active plugins for every single site
	 *
	 * @todo does not work with THX38 !!
	 *
	 * @since 1.0.0
	 *
	 * @see wp_get_theme()
	 * @see get_site_option()
	 * @see is_super_admin()
	 *
	 * @global array $actions
	 * @global string $theme
	 * @return array $actions
	 */
	public function action_links( $actions, $theme ){

		if ( !is_object( $theme ) ) {
			$theme = wp_get_theme( $theme );
		}

		// Get the toggle to see if users can view this information
		$allow = get_site_option( 'cets_theme_info_allow' );

		// if it's not the site admin and users aren't allowed to be in here, just get out.
		if ( $allow != 1 || !is_super_admin() ) {
			return $actions;
		}

		//get the list of blogs for this theme
		$data = get_site_option( 'cets_theme_info_data' );

		if ( isset( $data[ $theme->stylesheet ] ) ) {
			$blogs = $data[ $theme->stylesheet ];
		} else {
			$blogs = array();
		}

		// get the first param of the actions var and add some more stuff before it	
		//$start = $actions[0];
		$name = str_replace( " ", "_", $theme['Name'] );
		
		$text = '<span style="color: #999;">' . __( 'Used on', 'wpmu-theme-usage-info' ) . ' ';
		if ( sizeOf( $blogs ) > 0) {
			$text .= '<a href="#TB_inline?height=155&width=300&inlineId='. $name . '" class="thickbox" title="' . $theme['Name'] . '">';
		}
		$text .= sizeOf( $blogs ) . ' ' . __( 'site', 'wpmu-theme-usage-info' );
		if ( sizeOf( $blogs ) != 1 ) {
			$text .= 's';
		}
		if ( sizeOf( $blogs ) > 0 ) {
			$text .= '</a>';
			$text .= '<div id="' . $name . '" style="display: none"><div>';

			// loop through the list of blogs and display their titles
			$text .= '<h4>' . __( 'This theme is active on the following sites:', 'wpmu-theme-usage-info' ) . '<h4>' . ' <ul>';
			foreach ( $blogs as $blog ){
				$text .= '<li><a href="http://' . $blog['blogurl'] . '" target="new">' . $blog['name'] . '</a></li>';
			}
			$text .= '</li>';
			$text .= '</div></div>';
		}
		$text .= '</span>';

		array_push( $actions, $text );
		
		return $actions;
		
	} // END action_links()
	
	/**
	 * Add the menu item
	 *
	 * @since 1.0.0
	 *
	 * @see add_submenu_page()
	 */
	public function network_admin_menu() {
		
		add_submenu_page(
			'themes.php',
			__( 'Theme Usage Info', 'wpmu-theme-usage-info' ),
			__( 'Theme Usage Info', 'wpmu-theme-usage-info' ),
			apply_filters( 'wpmu_theme_usage_info_cap', 'manage_network' ),
			'wpmu-theme-usage-info',
			array( $this, 'theme_info_page' )
		);
		
	} // END network_admin_menu()
	
	/**
	 * Create a function to actually display stuff on plugin usage
	 *
	 * @since 1.0.0
	 *
	 * @param string $active_tab Defaults to ''
	 */
	public function theme_info_page( $active_tab = '' ) {
		
		$this->maybe_update();

		//Handle updates
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'update' ) {
			update_site_option( 'cets_theme_info_allow', $_POST['usage_flag'] );
		?>
			<div id="message" class="updated fade">
				<p>
					<?php _e( 'Settings saved.' ) ?>
				</p>
			</div>
		<?php
		}

		// get the usage setting
		$usage_flag = get_site_option( 'cets_theme_info_allow' );

		// if it's not set, set it to zero (the default)
		if ( strlen( $usage_flag ) == 0 ) {
			$usage_flag = 0;
			update_site_option( 'cets_theme_info_allow', 0 );
		}

		// Get the time when the theme list was last generated
		$gen_time = get_site_option( 'cets_theme_info_data_freshness' );

		if ( ( time() - $gen_time ) > 3600 ) {
			// if older than an hour, regenerate, just to be safe
			$this->generate_theme_blog_list();
		}

		$allowed_themes = WP_Theme::get_allowed_on_network();

		// returns an array of Theme Objects
		$themes = wp_get_themes();

		$list = get_site_option( 'cets_theme_info_data' );
		ksort( $list );
		
		// figure out what themes have not been used at all
//		$unused_themes = array();
//		foreach ( $themes as $theme ) {
//			if ( !array_key_exists( $theme->stylesheet, $list ) ) {
//				//if (!array_key_exists($theme['Name'], $list))
//				array_push($unused_themes, $theme);
//
//			}
//		}
		?>
		<style type="text/css">
			.bloglist {
				display:none;
			}
			.plugins .active td.theme-title {
				border-left: 4px solid #2EA2CC;
				font-weight: 700;
			}
		</style>
		<div class="wrap">
			<h2><?php _e( 'Theme Usage Information', 'wpmu-theme-usage-info' ); ?></h2>
			<?php
			if ( isset( $_GET['tab'] ) ) {
				$active_tab = $_GET['tab'];
			} else if ( $active_tab == 'settings' ) {
				$active_tab = 'settings';
			} else {
				$active_tab = 'themes';
			}
			?>

			<h3 class="nav-tab-wrapper">
				<a href="<?php echo add_query_arg( array( 'page' => 'wpmu-theme-usage-info', 'tab' => 'themes' ), network_admin_url( 'themes.php' ) ) ?>" class="nav-tab <?php echo $active_tab == 'themes' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Themes', 'wpmu-theme-usage-info' ); ?></a>
				<a href="<?php echo add_query_arg( array( 'page' => 'wpmu-theme-usage-info', 'tab' => 'settings' ), network_admin_url( 'themes.php' ) ) ?>" class="nav-tab <?php echo $active_tab == 'settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Settings', 'wpmu-theme-usage-info' ); ?></a>
			</h3><!-- .nav-tab-wrapper -->

			<?php if ( $active_tab == 'settings' ) { ?>
				<div class="tab-body">
					<h2><?php _e( 'Manage User Access', 'wpmu-theme-usage-info' ); ?></h2>
					<p><?php _e( 'Users can see usage information for themes in Appearance -> Themes. You can control user access to that information via this toggle.', 'wpmu-theme-usage-info' ); ?></p>
					<form name="themeinfoform" action="" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php _e( 'Let Users View Theme Usage Information:', 'wpmu-theme-usage-info' ); ?> </th>
									<td>
										<label>
											<input type="radio" name="usage_flag" value="1" <?php checked( '1', $usage_flag ) ?> />
											<?php _e( 'Yes' ) ?>
										</label>
										<br/>
										<label>
											<input type="radio" name="usage_flag" value="0" <?php checked( '0', $usage_flag ) ?> />
											<?php _e( 'No' ) ?>
										</label>
									</td>
								</tr>
							</tbody>
						</table>		
						<p>
							<input type="hidden" name="action" value="update" />
							<input type="submit" class="button-primary" name="Submit" value="<?php _e( 'Save Changes' ); ?>" />
						</p>
					</form>
				</div>

				<?php } else { ?>

				<div class="tab-body">
					<table class="wp-list-table widefat plugins" id="wpmu-active-themes">
						<thead>
							<tr>
								<th class="nocase">
									<?php _e( 'Themes', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th class="case" style="text-align: center !important;">
									<?php _e( 'Activated Sitewide', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th class="num">
									<?php _e( 'Total Sites', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th>
									<?php _e( 'Site Titles', 'wpmu-theme-usage-info' ); ?>
								</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th class="nocase">
									<?php _e( 'Themes', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th class="case" style="text-align: center !important;">
									<?php _e( 'Activated Sitewide', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th class="num">
									<?php _e( 'Total Sites', 'wpmu-theme-usage-info' ); ?>
								</th>
								<th>
									<?php _e( 'Site Titles', 'wpmu-theme-usage-info' ); ?>
								</th>
							</tr>
						</tfoot>
						<tbody id="themes">
							<?php
							$counter = 0;
							foreach ( $list as $theme => $blogs ) {
								$theme_object = wp_get_theme( $theme );
								$counter = $counter + 1;
								$thisTheme = $themes[ $theme ];
								$is_activated_sitewide = ( array_key_exists( $thisTheme['Stylesheet'], $allowed_themes ) ) ? true : false;
								?>
								<tr valign="top" class="<?php echo $is_activated_sitewide ? 'active' : 'inactive'; ?>">
									<td class="theme-title">
										<?php echo $theme_object->name; ?>
									</td>
									<td class="num">
										<?php
										// get the array for this theme
										if ( isset( $thisTheme ) ) {
											if ( $is_activated_sitewide ) {
												_e( 'Yes' );
											} else {
												_e( 'No' );
											}
										} else {
											_e( 'Theme Files Not Found!', 'wpmu-theme-usage-info' );
										}
										?>
									</td>
									<td class="num">
										<?php echo sizeOf( $blogs ); ?>
									</td>
									<td>
										<a href="javascript:void(0)" onClick="jQuery('#bloglist_<?php echo $counter; ?>' ).toggle(400);">
											<?php _e( 'Show/Hide Sites', 'wpmu-theme-usage-info' ); ?>
										</a>
										<ul class="bloglist" id="bloglist_<?php echo $counter; ?>">
											<?php
											foreach ( $blogs as $key => $row ) {
												$name[ $key ] = $row['name'];
												$blogurl[ $key ] = $row['blogurl'];
											}
											if ( sizeOf( $name ) == sizeOf( $blogs ) ) {
												array_multisort( $name, SORT_ASC, $blogs );
											}

											foreach ( $blogs as $blog ) {
												echo '<li><a href="http://' . $blog['blogurl'] . '" target="new">' . $blog['name'] . '</a></li>';
											}
											?>
										</ul>
									</td>
							<?php } // END foreach ( $list as $theme => $blogs )
							?>
						</tbody>
					</table>
				</div> <!--.tab-body-->
				<?php } ?>
		</div>
		
	<?php
	} // END theme_info_page()
	
	/**
	 * Regenerate the statistics on every theme switch network-wide
	 *
	 * @since 1.0.0
	 *
	 * @uses generate_plugin_blog_list()
	 * @action switch_theme
	 */
	public function switch_theme() {

		$this->generate_theme_blog_list();
		
	} // END switch_theme()
	
	/**
	 * Load assets on the page
	 *
	 * @since 1.0.0
	 *
	 * @see wp_enqueue_script()
	 * @see plugins_url()
	 * @action load-themes_page_wpmu-theme-usage-info
	 * @hook filter wpmu_theme_usage_info_debug	Defaults to {@see WP_DEBUG}
	 */
	public function load_admin_assets() {
		
		$dev = apply_filters( 'wpmu_theme_usage_info_debug', WP_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'tablesort', plugins_url( 'assets/js/tablesort' . $dev . '.js', __FILE__ ), array(), '2.5', true );

	} // END load_admin_assets()
	
	/**
	 * Load the plugin's textdomain hooked to 'plugins_loaded'.
	 *
	 * @since 1.0.0
	 *
	 * @see load_plugin_textdomain()
	 * @see plugin_basename()
	 * @action plugins_loaded
	 */
	public function load_plugin_textdomain() {
		
		load_plugin_textdomain(
			'wpmu-theme-usage-info',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages/'
		);
		
	} // END load_plugin_textdomain()
	
	/**
	 * Add link to the GitHub repo to the plugin listing
	 *
	 * @since 1.0.0
	 *
	 * @see plugin_basename()
	 *
	 * @param  array $links
	 * @param  string $file
	 * @return array $links
	 */
	public function plugin_row_meta( $links, $file ) {

		if ( $file == plugin_basename( __FILE__ ) ) {
			return array_merge(
				$links,
				array( '<a href="https://github.com/wp-repository/wpmu-theme-usage-info" target="_blank">GitHub</a>' )
			);
		}

		return $links;
		
	} // END plugin_row_meta()

	/**
	 * Pre-Activation checks
	 *
	 * Checks if this is a multisite installation
	 *
	 * @since 2.0.0
	 */
	public static function activation() {

		if ( ! is_multisite() ) {
			wp_die( __( 'This plugin only runs on multisite installations. The functionality makes no sense for WP single sites.', 'wpmu-theme-usage-info' ) );
		}

		// Delete legacy options
		delete_site_option( 'cets_theme_info_data' );
		delete_site_option( 'cets_theme_info_data_freshness' );

	} // END activation()

} // END class WPMU_Theme_Usage_Info

/**
 * Instantiate the main class
 *
 * @since 2.0.0
 *
 * @var object $wpmu_theme_usage_info Holds the instantiated class {@uses WPMU_Theme_Usage_Info}
 */
function WPMU_Theme_Usage_Info() {
	return WPMU_Theme_Usage_Info::instance();
} // END WPMU_Theme_Usage_Info()

WPMU_Theme_Usage_Info();
