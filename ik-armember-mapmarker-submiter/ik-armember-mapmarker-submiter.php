<?php
/*
Plugin Name: Armember to Mapmarker
Description: Sends Armember subscription to Map Marker
Version: 2.5.12
Author: Gabriel Caroprese
Requires at least: 5.3
Requires PHP: 7.2
*/ 

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

$ik_armember_mapDir = dirname( __FILE__ );
$ik_armember_mapPublicDir = plugin_dir_url(__FILE__ );
define( 'IK_ARMEMBER_MAP_DIR', $ik_armember_mapDir);
define( 'IK_ARMEMBER_MAP_PUBLIC', $ik_armember_mapPublicDir);

require_once($ik_armember_mapDir . '/include/init.php');
require_once($ik_armember_mapDir . '/include/menus.php');
require_once($ik_armember_mapDir . '/include/functions.php');
require_once($ik_armember_mapDir . '/include/ajax_functions.php');
require_once($ik_armember_mapDir . '/include/armember_licensekeys_class.php');
register_activation_hook( __FILE__, 'ik_armember_map_create_tables' );

?>