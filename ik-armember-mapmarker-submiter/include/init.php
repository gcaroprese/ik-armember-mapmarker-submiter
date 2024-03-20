<?php
/* 
Armember to Mapmarker Init Functions
Created: 16/11/2021
Last Update: 18/01/2022
Author: Gabriel Caroprese
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// if plugin YITH WooCommerce Subscription is not installed a message will show up
add_action( 'admin_notices', 'ik_armember_map_plugin_dependencies', 10);
function ik_armember_map_plugin_dependencies() {
    if (!class_exists('ARM_members') || !class_exists('MMP\Maps_Marker_Pro')) {
        $pluginURL = 'ik-armember-mapmarker-submiter/ik-armember-mapmarker-submiter.php';
        if (!class_exists('ARM_members')){
            echo '<div class="error"><p>' . __( 'Warning: The plugin "Armember to Mapmarker" needs <a href="https://wordpress.org/plugins/armember-membership/" target="_blank">ARMember</a> in order to work.' ) . '</p></div>';
            deactivate_plugins($pluginURL);   
        } else if (!class_exists('MMP\Maps_Marker_Pro')){
            echo '<div class="error"><p>' . __( 'Warning: The plugin "Armember to Mapmarker" needs <a href="https://www.mapsmarker.com/" target="_blank">Maps Marker Pro</a> in order to work.' ) . '</p></div>';
            deactivate_plugins($pluginURL);
        }
    }
}


//function to create tables in DB
function ik_armember_map_create_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_licence_keys = $wpdb->prefix . 'arm_ik_licence_keys';

	$sql = "CREATE TABLE ".$table_licence_keys." (
		id bigint(20) NOT NULL AUTO_INCREMENT,
		license_key varchar(60) NOT NULL,
		activation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		user_activated bigint(20) DEFAULT '0' NOT NULL,
	    activated int(1) DEFAULT '0' NOT NULL,
	    disabled int(1) DEFAULT '0' NOT NULL,
	    log longtext DEFAULT ' ' NOT NULL,
		UNIQUE KEY id (id)
	) ".$charset_collate.";"; 
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

//I add style and scripts from plugin
function ik_armember_map_add_css_js() {
	wp_register_style( 'ik_armember_map_css', IK_ARMEMBER_MAP_PUBLIC . 'css/stylesheet.css', false, '1.1.3', 'all' );
    wp_register_script( 'ik_armember_map_js', IK_ARMEMBER_MAP_PUBLIC . 'js/backend-script.js', '', '1.0.5', true );
	wp_enqueue_style('ik_armember_map_css');
	wp_enqueue_script( 'ik_armember_map_js' );
}
add_action( 'admin_enqueue_scripts', 'ik_armember_map_add_css_js' );

//Add script to set longitude and latitude
add_action( 'wp_enqueue_scripts', 'ik_armember_map_add_frontend_js' );
function ik_armember_map_add_frontend_js() {
    wp_enqueue_script('ik_armember_map_geoloc_script', IK_ARMEMBER_MAP_PUBLIC . '/js/geoloc_and_files.js', array(), '2.1.6', true );
    wp_localize_script( 'ik_armember_map_geoloc_script', 'ik_armember_ajaxurl', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

}
?>