<?php
/*

Armember to Mapmarker - Ajax Functions
Created: 04/12/2021
Last Update: 02/01/2022
Author: Gabriel Caroprese

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}


//Ajax to delete a license
add_action( 'wp_ajax_ik_armember_ajax_delete_license', 'ik_armember_ajax_delete_license');
function ik_armember_ajax_delete_license(){
    if(isset($_POST['iddata'])){
        $id_license = absint($_POST['iddata']);
        
        $licenses = new Ik_ArmemberLicenses();
        
        $licenses->delete($id_license);

        echo json_encode( true );
    }
    wp_die();         
}

//Ajax to edit license and show data about it
add_action( 'wp_ajax_ik_armember_ajax_edit_license', 'ik_armember_ajax_edit_license');
function ik_armember_ajax_edit_license(){
	if (isset($_POST['iddata'])){
	    
	    $id_license = absint($_POST['iddata']);

        if ($id_license > 0){

            $licenses = new Ik_ArmemberLicenses();
            
            $license_edit_info = $licenses->edit_license_info($id_license);
            
            if ($license_edit_info == false){
                $license_edit_info = '<p>Error retrieving info.</p>';
            }
    
            echo json_encode($license_edit_info);
        }
	} 
	wp_die();      
}

//Ajax to disable or enable license
add_action( 'wp_ajax_ik_armember_ajax_disable_enable_license', 'ik_armember_ajax_disable_enable_license');
function ik_armember_ajax_disable_enable_license(){
	if (isset($_POST['iddata']) && isset($_POST['statusid'])){
	    
	    $id_license = absint($_POST['iddata']);

        if ($id_license > 0){
	        $statusid = absint($_POST['statusid']);
	    
            $licenses = new Ik_ArmemberLicenses();
            
            $license_enable_status = $licenses->disable_license($id_license, $statusid);
    
            echo json_encode($license_enable_status);
        }
	} 
	wp_die();      
}

//Ajax to delete a username and data associated such as map marker and license usage
add_action( 'wp_ajax_ik_armember_ajax_delete_user', 'ik_armember_ajax_delete_user');
function ik_armember_ajax_delete_user(){
    //Default message
    $result = 'Username Not Deleted';
    if(isset($_POST['confirmation_delete'])){
        
        //I get current user ID
        $user_id = get_current_user_id();
  
        //I delete username
        wp_delete_user($user_id);
        
        $result = 'Username Deleted';
    }
    
    echo json_encode( $result );
    wp_die();         
}

//Ajax function to change name of uploaded file to change armFile to CubeMenu
add_action('wp_ajax_nopriv_ik_armember_ajax_update_filename', 'ik_armember_ajax_update_filename');
add_action( 'wp_ajax_ik_armember_ajax_update_filename', 'ik_armember_ajax_update_filename');
function ik_armember_ajax_update_filename(){

    if(isset($_POST['filepath']) && defined('MEMBERSHIP_UPLOAD_DIR')){
        
        $fileName = substr(esc_url($_POST['filepath']), strrpos(esc_url($_POST['filepath']), '/') + 1);
        
        update_option('option_file_test', $fileName);
        
        $newfileName = str_replace('armFile', 'CubeMenu', $fileName);
        
                update_option('option_file_test2', $newfileName);

        
        rename(MEMBERSHIP_UPLOAD_DIR.'/'.$fileName, MEMBERSHIP_UPLOAD_DIR.'/'.$newfileName);
        
        echo json_encode( true );
    }
    
    wp_die();         
}
?>