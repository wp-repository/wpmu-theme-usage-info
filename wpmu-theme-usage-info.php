<?php
/**
 * @author    Christian Foellmann & Jason Lemahieu and Kevin Graeme (Cooperative Extension Technology Services)
 * @copyright Copyright (c) 2014 - 2018 Christian Foellmann (http://christian.foellmann.de)
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package   WP-Repository\WPMU_Theme_Usage_Info
 * @version   3.0.0
 */
/*
Plugin Name: WPMU Theme Usage Info
Plugin URI: https://wordpress.org/plugins/wpmu-theme-usage-info/
Description: Provides info to network admins and users on the popularity of themes
Version: 3.0.0
Author: Christian Foellmann & Jason Lemahieu
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpmu-theme-usage-info
Domain Path: /languages
Network: true

	WPMU Theme Usage Info

	Copyright (C) 2014 - 2018 Christian Foellmann (http://christian.foellmann.de)
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
	public $version = '3.0.0';

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		/* Do nothing here */
	} // END __construct()

	public static function replacement() {
		add_action( 'network_admin_notices', array( __CLASS__, 'notice' ) );
	}

	public static function notice() {
		if ( self::replacement_active() ) {
			$plugin_link = self_admin_url( 'plugins.php?s=WPMU+Theme+Usage+Info&plugin_status=all' );
			?>
            <div class="notice notice-info">
                <p>
                    <code>Multisite Enhancements</code> is active. You can now remove <b>WPMU Theme Usage Info</b>. <a href="<?php echo $plugin_link; ?>">Goto Plugins</a>
                </p>
            </div>
			<?php
			return;
		}
		global $pagenow;
		if ( 'themes.php' == $pagenow ) {
			$plugin_name  = 'Multisite Enhancements';
			$details_link = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=multisite-enhancements&amp;TB_iframe=true&amp;width=600&amp;height=600' );
			$link         = '<a href="' . esc_url( $details_link ) . '" class="thickbox open-plugin-details-modal" aria-label="' . esc_attr( sprintf( __( 'More information about %s' ), $plugin_name ) ) . '" data-title="' . esc_attr( $plugin_name ) . '">' . $plugin_name . '</a>';
			?>
            <div class="notice notice-info">
                <p><b>WPMU Theme Usage Info</b> has reached its end-of-life date (<?php echo date_i18n( get_option( 'date_format' ), strtotime( '31.01.2018' ) ); ?>)! No updates and/or support is available.</p>
                <p>You should move to <?php echo $link; ?>.</p>
            </div>
			<?php
			return;
		}
		$themes_page = network_admin_url( 'themes.php' );
		?>
        <div class="notice notice-info">
            <p><b>WPMU Theme Usage Info</b> has reached its end-of-life date (<?php echo date_i18n( get_option( 'date_format' ), strtotime( '31.01.2018' ) ); ?>)! Go to <a href="<?php echo $themes_page; ?>"><?php _e( 'Installed Themes' ); ?></a> for
                more details.</p>
        </div>
		<?php
	}

	public static function replacement_active() {

		if ( class_exists( 'Multisite_Add_Theme_List' ) ) {
			return true;
		}

		return false;

	}

} // END class WPMU_Theme_Usage_Info

WPMU_Theme_Usage_Info::replacement();
