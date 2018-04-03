<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Image Effect
 * @author    Kevin Lee <kevixli@yahoo.com.hk>
 * @license   GPL-2.0+
 * @link      http://kevix.rf.gd/wordpress-plugin/
 * @copyright 2018 Kevin Lee
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if ( is_multisite() ) {

	$blogs = $wpdb->get_results( "SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A );
	delete_site_option( 'image_effects_settings' );

	if ( $blogs ) {

		foreach ( $blogs as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			delete_option( 'image_effects_settings' );

			//info: optimize table
			$GLOBALS['wpdb']->query( "OPTIMIZE TABLE `" . $GLOBALS['wpdb']->prefix . "options`" );
			restore_current_blog();
		}
	}

} else {
	delete_option( 'image_effects_settings' );

	//info: optimize table
	$GLOBALS['wpdb']->query( "OPTIMIZE TABLE `" . $GLOBALS['wpdb']->prefix . "options`" );
}