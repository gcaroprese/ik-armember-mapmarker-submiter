<?php
/* 
Armember to Mapmarker - License Key Manager Page
Created: 26/11/2021
Last Update: 18/01/2022
Author: Gabriel Caroprese
*/

if ( ! defined('ABSPATH')) exit('restricted access');


$qtyListing = 50;

// I check listing page
$paging = 1;
if (isset($_GET["listing"])){
    // I check if value is integer to avoid errors
    if (strval($_GET["listing"]) == strval(intval($_GET["listing"])) && $_GET["listing"] > 0){
        $paging = intval($_GET["listing"]);
    }
}


if (isset($_GET['status'])){
    if (isset($_GET['status'])){
        $status_filter = sanitize_text_field($_GET['status']);
        $filter['status'] = $status_filter;
    }
} else {
    $filter = false;
}


// I get the value for order of listing
$orderby = 'id';
if (isset($_GET["order"])){
    if ($_GET["order"] == 'id'){
        $orderby = 'id';
    } else if ($_GET["order"] == 'license_key'){
        $orderby = 'license_key';
    } else if ($_GET["order"] == 'activation_date'){
        $orderby = 'activation_date';
    } else if ($_GET["order"] == 'user_activated'){
        $orderby = 'user_activated';
    } else if ($_GET["order"] == 'activated'){
        $orderby = 'activated';
    } else if ($_GET["order"] == 'disabled'){
        $orderby = 'disabled';
    }
}

// I get the value for order of listing
$orderdir = 'desc';
if (isset($_GET["orderdir"])){
    if ($_GET["orderdir"] != 'desc'){
        $orderdir= 'asc';
    }
}

//If submits a new license key
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($_POST['license_key']) ){
        $license_keys = $_POST['license_key'];

        
        // I check if $license_keys is array or not. If not I don't import because it should be set as an array
        if (is_array($license_keys)){
    
            foreach( $license_keys as $license_key ) {
    
                if (isset($license_key)) {
                    $license_key = sanitize_text_field($license_key);

					global $wpdb;
					$data_license  = array (
					    'license_key' => $license_key,
					);

					$license_table = $wpdb->prefix.'arm_ik_licence_keys';
					$rowResult = $wpdb->insert($license_table,  $data_license , $format = NULL);
				
    			}
            }
        }
    }
}
?>
<div id="ik_armember_map_add_licenses">
    <h1>CubeMenu Keys</h1>
    <form action="" method="post" enctype="multipart/form-data" autocomplete="no">
        <div class="ik_armember_license_fields">
            <p>
                <span>Don't forget to use the metakey "license_key" on your ARMember Form.</span>
            </p>
            <ul>
				<li>
				    <input type="text" required name="license_key[]" placeholder="License Key" /> <a disabled href="#" class="ik_armember_delete_field button" style="opacity: 0">Delete</a>
				</li>
            </ul>
            <a href="#" class="button button-primary" id="ik_armember_add_field">Add Fields</a>
        </div>
        <input type="submit" class="button button-primary" value="Save" />
    </form>
</div>
<div id ="ik_armember_existing_licenses">
<?php
	//I list existing licenses
	$licenses = new Ik_ArmemberLicenses();
	$licenses_list = $licenses->get_list($qtyListing, $paging, $filter, $orderby, $orderdir);
	if ($licenses_list != false){
		echo $licenses_list;
	}
?>
</div>