<?php
global	$wpdb;
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

update_option( 'image_effects_version' , '1.0.0' );

// Create processed Images' Directory
$folderName = "image_effect_image";

$wq_upload_dir = wp_upload_dir();
wp_mkdir_p( $wq_upload_dir['basedir'] . '/'.$folderName.'/' );

chmod( $wq_upload_dir['basedir'], 0755 );
chmod( $wq_upload_dir['basedir'] . '/'.$folderName.'/', 0755 );

// Create Settings
$image_effects_settings = get_option( 'image_effects_settings' );
if ( false === $image_effects_settings ) {
	// Create Options
	$image_effects_settings = array(
			'save_temp_file_dir' 		=> get_temp_dir(),
			'processed_image_folder'	=> $wq_upload_dir['basedir'] . '/'.$folderName.'/',
			'test2'						=> array( 'fb', 'tw'),
	);

	update_option( 'image_effects_settings', $image_effects_settings );
}

flush_rewrite_rules();
?>