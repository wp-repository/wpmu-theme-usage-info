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
Description: Provides info to network admins and users on the popularity of themes
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
		add_action( 'admin_head-themes.php', array( $this, 'add_css'                ) );
		add_action( 'switch_theme',          array( $this, 'switch_theme'           ) );
		add_action( 'plugins_loaded',        array( $this, 'load_plugin_textdomain' ) );
		add_action( 'load-themes-network',   array( $this, 'load'                   ) );
		add_action( 'manage_themes_custom_column', array( $this, 'single_row' ), 10, 3 );

		/** Filters ***********************************************************/
		add_filter( 'plugin_row_meta',    array( $this, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'manage_themes-network_columns', array( $this, 'add_columns' ) );
		add_filter( 'wp_prepare_themes_for_js', array( $this, 'overlay' ) );

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

	public function overlay( $prepared_themes ) {
		
		$network_data = get_site_transient( 'theme_stats_data' );
		
		foreach ( $prepared_themes as $theme => $key ) {

			$data         = isset( $network_data[ $theme ] ) ? $network_data[ $theme ] : 0;
			$active_count = isset( $network_data[ $theme ] ) ? sizeOf( $data ) : 0;
			
			$prepared_themes[ $theme ]['version'] .= ' | ' . sprintf( _n( 'Active on %d site', 'Active on %d sites', $active_count, 'wpmu-theme-usage-info' ) , $active_count );

		}

		return $prepared_themes;
	}

	function load() {

		$theme_stats_data = get_site_transient( 'theme_stats_data' );

		if ( ! $theme_stats_data )  {
			$theme_stats_data = $this->generate_theme_blog_list();
		}

	}

	public function add_css() {
		?>
<style type="text/css">
	.column-active p { width: 200px; }
	.bloglist {	display:none; }
</style>
		<?php
	}

	public function add_columns( $columns ) {

		$columns['active'] = __( 'Usage', 'wpmu-theme-usage-info' );

		return $columns;
	}

	/**
	 * Fires inside each custom column of the Multisite themes list table.
	 *
	 * @since 3.1.0
	 *
	 * @param string   $column_name Name of the column.
	 * @param string   $stylesheet  Directory name of the theme.
	 * @param WP_Theme $theme       Current WP_Theme object.
	 */
	function single_row( $column_name, $stylesheet, $theme ) {

		$network_data = get_site_transient( 'theme_stats_data' );
		$data         = isset( $network_data[ $stylesheet ] ) ? $network_data[ $stylesheet ] : 0;
		$active_count = isset( $network_data[ $stylesheet ] ) ? sizeOf( $data ) : 0;
		
		switch ( $column_name ) {
			case 'active':
				echo '<p>' . sprintf( _n( 'Active on %d site', 'Active on %d sites', $active_count, 'wpmu-theme-usage-info' ) , $active_count ) . '</p>';
				$this->active_blogs_list( $data, $stylesheet );
				break;
		}

	}

	/**
	 * Fetch sites and the active themes for every single site
	 *
	 * @todo fetch all themes and list them with number of blogs even if count == 0
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
	 * @return array
	 */
	private function generate_theme_blog_list() {

		global $wpdb, $current_site;

		$select     = $wpdb->prepare( "SELECT blog_id, domain, path FROM $wpdb->blogs WHERE site_id = %d ORDER BY domain ASC", $current_site->id );
		$blogs      = $wpdb->get_results( $select );
		$blogthemes = array();
		$processedthemes = array();

		if ( $blogs ) {
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog->blog_id );
				$cto = wp_get_theme();
				$ct  = $cto->stylesheet;

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

		ksort( $blogthemes );
		set_site_transient( 'theme_stats_data', $blogthemes, 24 * HOUR_IN_SECONDS );

		return $blogthemes;

	} // END generate_theme_blog_list()

	/**
	 * List all sites the theme is active on
	 *
	 * @since 2.0.0
	 *
	 * @param  array $info
	 * @return string
	 */
	function active_blogs_list( $info, $id ) {

//		foreach ( $info as $key => $row ) {
//			$name[ $key ]    = $row['name'];
//			$blogurl[ $key ] = $row['blogurl'];
//		}
//		if ( sizeOf( $name ) == sizeOf( $info ) ) {
//			array_multisort( $name, SORT_ASC, $info );
//		}
		?>
		<a href="javascript:void(0)" onClick="jQuery('#bloglist_<?php echo esc_attr( $id ); ?>').toggle(400);">
			<?php _e( 'Show/Hide Blogs', 'wpmu-plugin-stats' ); ?>
		</a>
		<ul class="bloglist" id="bloglist_<?php echo esc_attr( $id ); ?>">
			<?php
			if ( isset( $info ) && is_array( $info ) ) {

				foreach ( $info as $blog ) {
					$link_title = empty( $blog['name'] ) ? $blog['url'] : $blog['name'];
					echo '<li><a href="http://' . $blog['blogurl'] . '" target="new">' . $link_title . '</a></li>';
				}
			} else {
				echo '<li>' . esc_html__( 'N/A', 'wpmu-plugin-stats' ) . '</li>';
			}
			?>
		</ul>
		<?php
	} // END active_blogs_list()

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
