<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/* 
Armember to Mapmarker Functions 
Created: 16/11/2021
Last Update: 17/01/2022
Author: Gabriel Caroprese
*/


//Function to list register form ID from ARMember plugin
function ik_armember_map_forms_option_list($type_form = 'registration'){
    
    if ($type_form != 'edit_profile'){
        $type_form = 'registration';
    }

    $defaultOption = '<option value="0">Select Form ID</option>';
    
    $options = $defaultOption;
    
    global $wpdb;
    $armemberforms = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "arm_forms WHERE arm_form_type LIKE '".$type_form."' ORDER BY arm_form_id DESC");
    
    if (isset($armemberforms[0]->arm_form_id)){
        foreach ($armemberforms as $armemberform){
            $options .= '<option value="'.$armemberform->arm_form_id.'">'.$armemberform->arm_form_label.' - #'.$armemberform->arm_form_id.'</option>';
        }
    }
    
    return $options;

}

//Function to list maps ID to assign map markers
function ik_armember_map_id_option_list(){
    $defaultOption = '<option value="0">Select Map ID</option>';
    
    $options = $defaultOption;
    
    global $wpdb;
    $armembermaps = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "mmp_maps ORDER BY id DESC");
    
    if (isset($armembermaps[0]->id)){
        foreach ($armembermaps as $armembermap){
            $options .= '<option value="'.$armembermap->id.'">'.$armembermap->name.' - #'.$armembermap->id.'</option>';
        }
    }
    
    return $options;

}

add_action( 'arm_after_add_new_user', 'ik_armember_map_import_userdata_to_mapmarker', 10, 2);
function ik_armember_map_import_userdata_to_mapmarker($user_id = 0, $posted_register_data = array()) {
    
    //I get he form ID configured to import form data to map marker
    $form_id_to_import = get_option('ik_armember_map_form_id');
    
    //I make sure the valuables to assign exist
    $business_name = (isset($posted_register_data['business_name'])) ? $posted_register_data['business_name'] : '';
    $latitude = (isset($posted_register_data['latitude'])) ? $posted_register_data['latitude'] : '';
    $longitude = (isset($posted_register_data['longitude'])) ? $posted_register_data['longitude'] : '';

    //If form ID matches to the submited form
    if ($posted_register_data['arm_form_id'] == $form_id_to_import){
        if (isset($posted_register_data['file_reference']) && isset($posted_register_data['url_reference'])){
            if ($posted_register_data['file_reference'] != NULL || $posted_register_data['file_reference'] != ''){
                $link_mapmarker = $posted_register_data['file_reference'];
            } else {
                $link_mapmarker = $posted_register_data['url_reference'];
            }
        } else if (isset($posted_register_data['file_reference'])){
            $link_mapmarker = $posted_register_data['file_reference'];
        } else {
            if (isset($posted_register_data['url_reference'])){
                $link_mapmarker = $posted_register_data['url_reference'];                
            } else {
                $link_mapmarker = '';
            }
        }
    
        
        //I import data to a map marker record
        global $wpdb;
        $tableInsert = $wpdb->prefix.'mmp_markers';
        $data_map  = array (
                        'name'=>$business_name,	
                        'lat'=>$latitude,	
                        'lng'=> $longitude,	
                        'zoom'=>'14.0',	
                        'icon'=> ik_armember_map_get_map_marker_icon(),	
                        'link'=>$link_mapmarker,	
                        'blank'=> 1,	
                        'created_by_id'=>$user_id,	
                        'created_on'=>current_time( 'mysql' ),	
                        'updated_by_id'=>$user_id,	
                        'updated_on'=>current_time( 'mysql' ),	
                );
        $rowResult = $wpdb->insert($tableInsert, $data_map);
        $mapmarker_id = $wpdb->insert_id;
        
        update_user_meta($user_id, 'ik_armember_map_marker_id', $mapmarker_id);
        
        //If there's a map assigned I assign the map marker to a map ID
        $map_id_to_assign = absint(get_option('ik_armember_map_map_id'));
        
        if ($map_id_to_assign == 0 ){
            $map_id_to_assign = 1;
        }

        //I assigned the map marker to the map ID
        global $wpdb;
        $tableInsert = $wpdb->prefix.'mmp_relationships';
        $data_map_relationship  = array (
                        'map_id'=> $map_id_to_assign,	
                        'type_id'=> '2',	
                        'object_id'=> $mapmarker_id,	
                );
        $rowResult = $wpdb->insert($tableInsert, $data_map_relationship);        

        
        //If there's a licenses registered
        if (isset($posted_register_data['license_key'])){

            $license_key = sanitize_text_field($posted_register_data['license_key']);
    
            //I get the license id 
            $licenses = new Ik_ArmemberLicenses();
            $license = $licenses->get_license($license_key, 'license_key');
        
        
            // I update the license key adding the user that activated the key
            global $wpdb;
            $license_key_table = $wpdb->prefix.'arm_ik_licence_keys';
            $where = [ 'id' => $license[0]->id ];
                
            $license_update_data  = array (
                            'user_activated'=> $user_id,
                    );
            $rowResult = $wpdb->update($license_key_table,  $license_update_data , $where);                 

        }        
    }
    
}


add_action( 'arm_update_profile_external', 'ik_armember_map_update_userdata_on_mapmarker', 10, 2 );
function ik_armember_map_update_userdata_on_mapmarker($user_id, $form_data) {

    $map_marker_id = get_user_meta($user_id, 'ik_armember_map_marker_id', true);
    
    //If there's a map associated to the username
    if ($map_marker_id != false && $map_marker_id != NULL){
        
        //I make sure the valuables to assign exist
        if (isset($form_data['business_name']) && isset($form_data['latitude']) && isset($form_data['longitude'])){
        
            if (isset($form_data['file_reference']) && isset($form_data['url_reference'])){
                if ($form_data['file_reference'] != NULL || $form_data['file_reference'] != ''){
                    $link_mapmarker = $form_data['file_reference'];
                } else {
                    $link_mapmarker = $form_data['url_reference'];
                }
            } else if (isset($form_data['file_reference'])){
                $link_mapmarker = $form_data['file_reference'];
            } else {
                if (isset($form_data['url_reference'])){
                    $link_mapmarker = $form_data['url_reference'];                
                } else {
                    $link_mapmarker = '';
                }
            }
    
            global $wpdb;
            $tableupdate = $wpdb->prefix.'mmp_markers';
            $where = [ 'id' => $map_marker_id ];
                
            $data_map_updated  = array (
                            'name'=>$form_data['business_name'],	
                            'lat'=>$form_data['latitude'],	
                            'lng'=> $form_data['longitude'],	
                            'zoom'=>'14.0',	
                            'icon'=> ik_armember_map_get_map_marker_icon(),	
                            'link'=>$link_mapmarker,
                            'blank'=> 1,	
                            'updated_by_id'=>$user_id,
                            'updated_on'=>current_time( 'mysql' ),	
                    );
            $rowResult = $wpdb->update($tableupdate,  $data_map_updated , $where);  
            
            
        }
        
    } else {
        
        //I make sure the valuables to assign exist
        $business_name = (isset($form_data['business_name'])) ? $form_data['business_name'] : '';
        $latitude = (isset($form_data['latitude'])) ? $form_data['latitude'] : '';
        $longitude = (isset($form_data['longitude'])) ? $form_data['longitude'] : '';
    
        //If form date matches
        if (isset($form_data['file_reference']) && isset($form_data['url_reference'])){
            if ($form_data['file_reference'] != NULL || $form_data['file_reference'] != ''){
                $link_mapmarker = $form_data['file_reference'];
            } else {
                $link_mapmarker = $form_data['url_reference'];
            }
        } else if (isset($form_data['file_reference'])){
            $link_mapmarker = $form_data['file_reference'];
        } else {
            if (isset($form_data['url_reference'])){
                $link_mapmarker = $form_data['url_reference'];                
            } else {
                $link_mapmarker = '';
            }
        }
    
        //I import data to a map marker record
        global $wpdb;
        $tableInsert = $wpdb->prefix.'mmp_markers';
        $data_map  = array (
                        'name'=>$business_name,	
                        'lat'=>$latitude,	
                        'lng'=> $longitude,	
                        'zoom'=>'14.0',	
                        'icon'=> ik_armember_map_get_map_marker_icon(),	
                        'link'=>$link_mapmarker,	
                        'blank'=> 0,	
                        'created_by_id'=>$user_id,	
                        'created_on'=>current_time( 'mysql' ),	
                        'updated_by_id'=>$user_id,	
                        'updated_on'=>current_time( 'mysql' ),	
                );
        $rowResult = $wpdb->insert($tableInsert, $data_map);
        $mapmarker_id = $wpdb->insert_id;
        
        update_user_meta($user_id, 'ik_armember_map_marker_id', $mapmarker_id);
        
        //If there's a map assigned I assign the map marker to a map ID
        $map_id_to_assign = absint(get_option('ik_armember_map_map_id'));
        
        if ($map_id_to_assign == 0 ){
            $map_id_to_assign = 1;
        }

        //I assigned the map marker to the map ID
        global $wpdb;
        $tableInsert = $wpdb->prefix.'mmp_relationships';
        $data_map_relationship  = array (
                        'map_id'=> $map_id_to_assign,	
                        'type_id'=> '2',	
                        'object_id'=> $mapmarker_id,	
                );
        $rowResult = $wpdb->insert($tableInsert, $data_map_relationship);        

        
        //If there's a licenses registered
        if (isset($form_data['license_key'])){

            $license_key = sanitize_text_field($form_data['license_key']);
    
            //I get the license id 
            $licenses = new Ik_ArmemberLicenses();
            $license = $licenses->get_license($license_key, 'license_key');
        
        
            // I update the license key adding the user that activated the key
            global $wpdb;
            $license_key_table = $wpdb->prefix.'arm_ik_licence_keys';
            $where = [ 'id' => $license[0]->id ];
                
            $license_update_data  = array (
                            'user_activated'=> $user_id,
                    );
            $rowResult = $wpdb->update($license_key_table,  $license_update_data , $where);                 

        }   
        
    }

}

//I validate license key before signing up
add_action( 'arm_validate_field_value_before_form_submission', 'ik_armember_map_validate_licensekey', 10, 3 );
function ik_armember_map_validate_licensekey($return, $form, $posted_data) {
    
    //I make sure there's a license key
    if(isset($posted_data['license_key'])) {
        
        //validate license key
        $licenses = new Ik_ArmemberLicenses();
        $license_key_validation = $licenses->validate_key($posted_data['license_key']);
    
        if ($license_key_validation == false){
            if(!is_array($return)) { $return = array(); }
            $return['license_key'] = 'License Key Invalid';
        } else {
            
            // I update the license key as activated
            global $wpdb;
            $license_key_table = $wpdb->prefix.'arm_ik_licence_keys';
            $where = [ 'id' => $license_key_validation ];
                
            $license_update_data  = array (
                            'activated'=> 1,
                            'activation_date'=>date('Y-m-d H:i:s'),
                    );
            $rowResult = $wpdb->update($license_key_table,  $license_update_data , $where); 
        }
    }       

    return $return;
}

//Option list of icons from map marker uploads folder for select 
function ik_armember_map_icons_list(){
    $upload_dir = wp_upload_dir();
    $mapmarkersdir = $upload_dir['basedir'].'/maps-marker-pro/icons';
    
    $icon_files = list_files($mapmarkersdir);
    
    if (is_array($icon_files)){
        sort($icon_files);
        $icons_select = '';
        foreach($icon_files as $icon_file){
            $url_icon_array = explode('/', $icon_file);
            $icon_file = end($url_icon_array);
            $icon_src = $upload_dir['baseurl'].'/maps-marker-pro/icons/'.$icon_file;
            $icons_select .= '<option value="'.$icon_file.'" style="background-image:url('.$icon_src.');">'.$icon_file.'</option>';
        }
    }
    
    return $icons_select;

}

//function to get map marker icon
function ik_armember_map_get_map_marker_icon(){
    $icon_file = get_option('ik_armember_map_map_icon');
    if ($icon_file == false || $icon_file == NULL ){
        //If no icon assigned I select first from the list
        $upload_dir = wp_upload_dir();
        $mapmarkersdir = $upload_dir['basedir'].'/maps-marker-pro/icons';
        
        $icon_files = list_files($mapmarkersdir);
        
        if (is_array($icon_files)){
            sort($icon_files);
            $url_icon_array = explode('/', $icon_files[0]);
            $icon_file = end($url_icon_array);
        }
    }
    
    return $icon_file;

}


//Shortcode to show delete user button
function ik_armember_map_delete_user(){
    
    $output = '<a id="arm_form_field_delete_user_button" class="arm_form_field_container_button arm_btn_style_border arm_form_input_box arm_has_suffix_icon md-button md-ink-ripple" href="#">
    <span class="arm_spinner ng-scope">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="18px" height="18px" viewBox="0 0 26.349 26.35" 
    style="enable-background:new 0 0 26.349 26.35;" xml:space="preserve"><g><g><circle cx="13.792" cy="3.082" r="3.082"></circle><circle cx="13.792" cy="24.501" r="1.849"></circle>
    <circle cx="6.219" cy="6.218" r="2.774"></circle><circle cx="21.365" cy="21.363" r="1.541"></circle><circle cx="3.082" cy="13.792" r="2.465"></circle>
    <circle cx="24.501" cy="13.791" r="1.232"></circle>
    <path d="M4.694,19.84c-0.843,0.843-0.843,2.207,0,3.05c0.842,0.843,2.208,0.843,3.05,0c0.843-0.843,0.843-2.207,0-3.05 C6.902,18.996,5.537,18.988,4.694,19.84z"></path><circle cx="21.364" 
    cy="6.218" r="0.924"></circle></g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g><g></g></svg>
    </span>Delete Profile</a>
    <style>
    .arm_form_input_wrapper #arm_form_field_delete_user_button, .arm_form_input_wrapper #arm_form_field_delete_user_button:not(:hover):not(:active):not(.has-background){
		text-align: center;
		margin: 30px auto;
		background: transparent;
		border-radius: 50px;
		-webkit-border-radius: 12px;
		-moz-border-radius: 50px;
		-o-border-radius: 50px;
		width: auto;
		max-width: 100%;
		width: 350px;
		min-height: 45px;
		border: 2px solid #000000;
		color: #000000;
		padding: 4px 10px 0;
		font-size: 16px;
		display: block;
		font-weight: bold;
		text-decoration: none;
		text-transform: uppercase;
		background-color: transparent;
    }
    .arm_form_input_wrapper #arm_form_field_delete_user_button:hover{
		background: #000;
		color: #fff;;
    }
    #arm_form_field_delete_user_button .arm_spinner {
        animation: arm_spin 1.5s linear infinite;
        -webkit-animation: arm_spin 1.5s linear infinite;
        -moz-animation: arm_spin 1.5s linear infinite;
        -o-animation: arm_spin 1.5s linear infinite;
        top: 1px;
        margin-right: 3px;
        position: relative;
    }
    </style>
    <script>
        jQuery("#arm_form_field_delete_user_button").parent().parent().parent().appendTo(".arm_form_edit_profile .arm_form_inner_container");
        jQuery("body").on("click", "#arm_form_field_delete_user_button", function(e){
            e.preventDefault();
            var button = jQuery(this);
        
            var message_question = "Are you sure you want to delete your username?";
            
            var confirmation_delete = confirm(message_question);

    
            if (confirmation_delete == true){
			
				jQuery(button).find(".arm_spinner").attr("style", "width: auto; opacity: 1");
            
    			var data = {
    				action: "ik_armember_ajax_delete_user",
    				"post_type": "post",
    				"confirmation_delete": confirmation_delete,
    			};  
    
    			jQuery.post( "'.admin_url('admin-ajax.php').'", data, function(response) {
    				if (response){								
                        jQuery(button).find(".arm_spinner").removeAttr("style");
                        alert(response);
    					window.location.replace("'.get_site_url().'");
    				} else {
    				    alert("error");
    				    location.reload();
    				}
				}, "json");            
            }
            return false;
        });
    </script>
    ';

    return $output;
    
}
add_shortcode('armember_delete_user', 'ik_armember_map_delete_user');


//Function to delete association with map marker and license key when user is deleted
function ik_armember_map_delete_user_association( $user_id ) {
    $user_id = absint($user_id);

    //I delete assoc with license, map and leave a log
    $licenses = new Ik_ArmemberLicenses();
    $licenses->delete_assoc($user_id);

}
add_action( 'delete_user', 'ik_armember_map_delete_user_association' );


//Function to delete the "armFile" from the uploaded file name
function ik_armember_map_change_filename_uploaded( $post_ID ) {

    $file = get_attached_file( $post_ID );
    $path = pathinfo( $file );

    $new_name = str_replace("armFile", "CubeMenu", $path['filename']);
    $new_file = $path['dirname'] . '/' . $new_name . '.' . $path['extension'];
    rename( $file, $new_file );    
    update_attached_file( $post_ID, $new_file );
}
add_action( 'add_attachment', 'ik_armember_map_change_filename_uploaded' );


//Function to delete redirection URL field if user type = 0 
add_filter( 'arm_change_content_after_display_form', 'ik_armember_map_remove_redirection_field', 10, 3);
function ik_armember_map_remove_redirection_field($content, $form, $atts){
    
    $current_user_id = get_current_user_id();
    
    //Default action is delete the form field
    $delete_url_reference = true;
    
    //I check the user ID type from armember members table
    global $wpdb;
    $query_usermember = "SELECT * FROM ".$wpdb->prefix."arm_members WHERE arm_user_id =".$current_user_id;
    $member = $wpdb->get_results($query_usermember);
    
    if (isset($member[0]->arm_user_type)){
        if ($member[0]->arm_user_type != 0){
            $delete_url_reference = false;
        }
    }
    
    if ($delete_url_reference == true){
        foreach ($form->fields as $key => $field){
            if ($field['arm_form_field_slug'] == 'url_reference'){
                $content = $content.'
                <script>
                jQuery("#arm_form_field_container_'.$field['arm_form_field_id'].'").remove();
                jQuery("#armember_map_test_url").parent().parent().parent().remove();
                </script>';
            }
        }
    }

    return $content;
}

?>