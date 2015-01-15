<?php
/**
 * @author    Christian Foellmann & Jason Lemahieu and Kevin Graeme (Cooperative Extension Technology Services)
 * @copyright Copyright (c) 2009 - 2014, Cooperative Extension Technology Services
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @package   WP-Repository\WPMU_Theme_Usage_Info
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

delete_site_option( 'cets_theme_info_version' );
delete_site_option( 'cets_theme_info_data_freshness' );
