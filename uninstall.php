<?php
/**
 * @author WP-Cloud <code@wp-cloud.de>
 * @license GPLv2 <http://www.gnu.org/licenses/gpl-2.0.html>
 * @package WPMU Theme Usage Info
 */

//avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

delete_site_option( 'cets_theme_info_version' );
delete_site_option( 'cets_theme_info_data_freshness' );
