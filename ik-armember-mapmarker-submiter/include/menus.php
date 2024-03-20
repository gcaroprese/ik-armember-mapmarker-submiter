<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* 
Armember to Mapmarker Menus
Created: 16/11/2021
Last Update: 20/12/2021
Author: Gabriel Caroprese
*/

// I add menus on WP-admin
add_action('admin_menu', 'ik_armember_map_wpmenu', 999);
function ik_armember_map_wpmenu(){
    add_submenu_page('arm_manage_members', 'CubeMenu Markers', 'CubeMenu Markers', 'manage_options', 'ik_armember_map_config_page', 'ik_armember_map_config_page', 2 );
    add_submenu_page('arm_manage_members', 'CubeMenu Keys', 'CubeMenu Keys', 'manage_options', 'ik_armember_map_licence_keys', 'ik_armember_map_licence_keys', 3 );
}

//Function to add config panel content
function ik_armember_map_config_page(){
    include (IK_ARMEMBER_MAP_DIR.'/templates/config.php');
}

//Function to add the licence key for registrarions editor page
function ik_armember_map_licence_keys(){
    include (IK_ARMEMBER_MAP_DIR.'/templates/license_manager.php');
}




?>