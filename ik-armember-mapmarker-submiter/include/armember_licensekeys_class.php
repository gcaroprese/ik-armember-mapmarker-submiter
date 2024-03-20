<?php
/*

Armember to Mapmarker - LicensesClass
Created: 16/11/2021
Last Update: 18/01/2022
Author: Gabriel Caroprese

*/

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

class Ik_ArmemberLicenses{
        
    //Function to return value of license
    public function get_license($license_data = 0, $by='id') {

        if ($by != 'id'){
            $by = 'license_key';
            $license_data = sanitize_text_field($license_data);
        } else {
            $license_data = absint($license_data);
        }
        
        global $wpdb;
        $query_license = "SELECT * FROM ".$wpdb->prefix."arm_ik_licence_keys WHERE ".$by." = '".$license_data."'";
        $license = $wpdb->get_results($query_license);
        
        // I check if value is not null 
        if (isset($license[0]->id)){
            return $license;
        }
    
        return false;
    }
    
    //Function to return data to edit about license
    public function edit_license_info($license_id = 0) {
        $license_id = absint($license_id);
        
        $license_data = $this->get_license($license_id);
        
                
        //I check if there's a log
        $log = $this->get_log_text($license_id);
        
        if ($license_data != false){
            if ($license_data[0]->activated == '0'){

                if ($log != ''){
                    $lincenseinfo = '<p>License key deactivated and ready to use.</p><p>'.$log.'</p>';
                } else {
                    $lincenseinfo = '<p>License key not activated yet</p>';
                }
                
            } else {
                if ($license_data[0]->user_activated == '0'){
                    
                    if ($log != ''){
                        $user_activated = '<p>License key not assigned to a username yet</p>';
                    } else {
                        $user_activated = '<p>License key not active.</ br></p><p>'.$log.'</p>';
                    }
                } else {
                    $user_id = $license_data[0]->user_activated;
                    $user = get_user_by( 'id', $user_id ); 
                    $user_activated = '<a href="'.get_site_url().'/wp-admin/user-edit.php?user_id='.$user_id.'">'.$user->user_login.'</a>';
                }
                
                $lincenseinfo = '<p>Activation Date: '.$license_data[0]->activation_date.'</p>
                <p>User Activated: '.$user_activated.'</p><p>'.$log.'</p>';                
            }
            return $lincenseinfo;
        }
        
        return false;
        
    }
    
    //function to validate license key
    public function validate_key($license_key){
        
        $license_key = sanitize_text_field($license_key);
        
        global $wpdb;
        $query_validate = "SELECT * FROM ".$wpdb->prefix."arm_ik_licence_keys WHERE license_key = '".$license_key."' AND disabled = '0' AND activated = '0'";
        $valid = $wpdb->get_results($query_validate);
        
        // I check if valid license exists
        if (isset($valid[0]->id)){
            return $valid[0]->id;
        } else {
            return false;
        }
        
    }
    
    //Function to delete license by id
    public function delete($license_id = 0) {
        $license_id = absint($license_id);
        
        global $wpdb;
        $table_delete = $wpdb->prefix.'arm_ik_licence_keys';
        $action_delete = $wpdb->delete( $table_delete , array( 'id' => $license_id ) );
    
        return;
    }
    
    //Function to delete association with deleted user
    public function delete_assoc($user_id = 0) {
        $user_id = absint($user_id);
        
        //I get the map marker matching with user ID
        global $wpdb;
    	$query_licenses_assoc = "SELECT id FROM ".$wpdb->prefix."arm_ik_licence_keys WHERE user_activated = '".$user_id."'";
    	$licenses_assoc = $wpdb->get_results($query_licenses_assoc);
        
        if (isset($licenses_assoc[0]->id)){
            
            //I take the name and ID of the map markers and then I delete the map marker and the meta data
            
            //I get the map marker matching with user ID
            global $wpdb;
        	$query_mapmarker = "SELECT id, name FROM ".$wpdb->prefix."mmp_markers WHERE created_by_id = '".$user_id."'";
        	$map_markers = $wpdb->get_results($query_mapmarker);  
            
            $licenseCount = 0;
            foreach ($licenses_assoc as $license_assoc){
                $restaurant_name = '';
                if (isset($map_markers[$licenseCount]->name)){
                    $restaurant_name = ' ('.$map_markers[$licenseCount]->name.')';
                }
                
                $log_data = 'User ID '.$user_id.$restaurant_name.' deleted at '.date("d-m-Y H:i:s").'.';
                
                $this->log($license_assoc->id, $log_data, true);
                
                $licenseCount = $licenseCount + 1;
                
            }
        }

        //I get the map marker matching with user ID to delete map markers separately from licenses association 
        global $wpdb;
    	$query_mapmarker = "SELECT id, name FROM ".$wpdb->prefix."mmp_markers WHERE created_by_id = '".$user_id."'";
    	$map_markers = $wpdb->get_results($query_mapmarker);  
    	
    	if (isset($map_markers[0]->name)){
        	foreach ($map_markers as $map_marker){
        	    global $wpdb;
                $table_delete = $wpdb->prefix.'mmp_markers';
                $action_delete = $wpdb->delete( $table_delete , array( 'id' => $map_marker->id ) );
                
                global $wpdb;
                $delete_metamap_query = "DELETE FROM ".$wpdb->prefix."mmp_relationships WHERE object_id = ".$map_marker->id;
                $wpdb->get_results($delete_metamap_query);
        	}
    	}
    
        return;
    }
    
    //Function to get a log
    private function get_log($license_id) {
        $license_id = absint($license_id);

        // I read the license data
        global $wpdb;
    	$query_licenses_log = "SELECT log FROM ".$wpdb->prefix."arm_ik_licence_keys WHERE id = '".$license_id."'";
    	$logs = $wpdb->get_results($query_licenses_log);
        
        if (isset($logs[0]->log)){
            $logs_data = $logs[0]->log;
            if (is_serialized($logs_data)){
                $logs_data = maybe_unserialize($logs_data);  
                
                return $logs_data;
            }
        }
    
        return false;
    }

    //Function to get a log text
    public function get_log_text($license_id) {
        
        $logs = $this->get_log($license_id);
        
        $log_text = '';
        if (is_array($logs)){
            foreach ($logs as $log){
                $log = sanitize_text_field($log);
                $log_text .= '<span class="ik_armember_map_css_log_data">'.$log.'</span>';
            }
        }
    
        return $log_text;
    }
    
    //Function to write a log for deletion or any purpose
    public function log($license_id, $log, $deletion = false) {
        $license_id = absint($license_id);
        $log = sanitize_text_field($log);
        
        //I get previous log data if exists
        $previous_log = $this->get_log($license_id);
        
        if ($previous_log != false){
            $log_data = $previous_log;
        }
        
        $log_data[] = $log;

        // I update the license log value
        global $wpdb;
        $license_key_table = $wpdb->prefix.'arm_ik_licence_keys';
        $where = [ 'id' => $license_id ];
            
        if ($deletion == false){
            $license_log_data  = array (
                            'log'=> maybe_serialize($log_data),
                    );
        } else {
            $license_log_data  = array (
                            'log'=> maybe_serialize($log_data),
                            'activation_date' => '0000-00-00 00:00:00',
                            'user_activated' => 0,
                            'activated' => 0
                    );
        }
        $rowResult = $wpdb->update($license_key_table,  $license_log_data , $where);            

    
        return;
    }

    //Function to enable or disable a license to be used
    public function disable_license($license_id = 0, $disabled = 0) {
        $license_id = absint($license_id);
        $disabled = absint($disabled);

        // I update the license log value
        global $wpdb;
        $license_key_table = $wpdb->prefix.'arm_ik_licence_keys';
        $where = [ 'id' => $license_id ];
            
        $enable_data  = array (
                            'disabled' => $disabled,
                    );
        $rowResult = $wpdb->update($license_key_table,  $enable_data , $where);    
        
        if ($disabled == 0){
            $next_action['text'] = 'Disable';
            $next_action['value'] = 1;
        } else {
            $next_action['text'] = 'Enable';
            $next_action['value'] = 0;
        }
    
        return $next_action;
    }
    
    //Function to return listing of licenses
    public function get_list($qty = '', $page = '0', $filter = '', $orderby = 'id', $orderdir = 'desc') {
        $qty = absint($qty);
        $page = absint($page);
        $offsetList = ($page - 1) * $qty;
        $where = '';
        $status_inactive = '';
    	$status_active = '';
    	
    	
    	// I get the value for order of listing
        $orderDir = 'DESC';
        $orderClass= 'sorted desc';
        if ($orderdir != 'desc'){
            $orderDir= 'ASC';
            $orderdir= 'asc';
            $orderClass= 'sorted';
        }
    	
        $empty = '';
        $idClass = $empty;
        $license_keyClass = $empty;
        $activation_dateClass = $empty;
        $user_activatedClass = $empty;
        $activatedClass = $empty;
        $disabledClass = $empty;
        
        if ($orderby != 'id'){
            if ($orderby == 'license_key'){
                $license_keyClass = $orderClass;
            } else if ($orderby == 'activation_date'){
                $activation_dateClass = $orderClass;
            } else if ($orderby == 'user_activated'){
                $user_activatedClass = $orderClass;
            } else if ($orderby == 'activated'){
                $activatedClass = $orderClass;
            } else {
                $disabledClass = $empty;
            }
        } else {
            $idClass = $orderClass;
        }
    	
    	
    	
    	if (is_array($filter)){
    		if (isset($filter['status'])){
    		    if ($filter['status'] != 0){
    		        $status = 1;
    		        $status_active = 'selected';
    		    } else {
    		        $status = 0;
    		        $status_inactive = 'selected';
    		    }
    		    
    		        $where = " WHERE activated = '".$status."'";
    		}
    	}
    	
    	if ($page > 0 && is_int($qty)){
    	    $offset = ' LIMIT '.$qty.' OFFSET '.$offsetList;
    	} else {
    	    $offset = '';
    	}

        //I check the total number of licenses
    	global $wpdb;
    	$query_licenses_all = "SELECT id FROM ".$wpdb->prefix."arm_ik_licence_keys".$where;
    	$licenses_all = $wpdb->get_results($query_licenses_all);
    	
    	
    	if (isset($licenses_all[0]->id)){
        	global $wpdb;
        	$query_licenses = "SELECT * FROM ".$wpdb->prefix."arm_ik_licence_keys".$where." ORDER BY ".$orderby." ".$orderDir." ".$offset;
        	$licenses = $wpdb->get_results($query_licenses);   
    	    
    	    if (isset($status)){
    	        $status_url = '&status='.$status;
    	    } else {
    	        $status_url = '';
    	    }    
    	    
    	    $total_licenses = count($licenses_all);

    	    //I check the page number
    	    if ($page > 1){
    	        $pagen = $page - 1;
    	    } else {
    	        $pagen = 0;
    	    }
    	    
		    $url_licensesadmin_unfiltered = get_site_url().'/wp-admin/admin.php?page=ik_armember_map_licence_keys';
		    $url_licensesadmin_unlisted = $url_licensesadmin_unfiltered.$status_url;
		    $url_licensesadmin = $url_licensesadmin_unlisted.'&listing='.$pagen;
		    
		    $table_head = '<p id="ik_armember_filter_box">
                    <span>Show:</span>
                    <select name="filter_activation" onchange="location = this.value;">
                        <option data="all" value="'.$url_licensesadmin_unfiltered.'">All</option>
                        <option data="0" '.$status_active.' value="'.$url_licensesadmin_unlisted.'&status=1">Active</option>
                        <option data="1" '.$status_inactive.' value="'.$url_licensesadmin_unlisted.'&status=0">Inactive</option>
                    </select>
                    <span class="data_licenses_list">Total: '.$total_licenses.' licenses.</span>
    			</p>	
    			<table>
    					<thead>
    						<tr>
    							<th><input type="checkbox" class="select_all" /></th>
    							<th class="orderitem '.$idClass.'" order="id">
    							    <div class="sorting"><span>ID</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$license_keyClass.'" order="license_key">
    							    <div class="sorting"><span>License Key</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$activatedClass.'" order="activated">
    							    <div class="sorting"><span>Activated</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$activation_dateClass.'" order="activation_date">
    							    <div class="sorting"><span>Activation Date</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$disabledClass.'" order="disabled">
    							    <div class="sorting"><span>Status</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th><a href="#" class="ik_armember_button_delete_selected button action">Remove</a></th>
    						</tr>
    					</thead>
    				<tbody>';
    				
    		$table_foot = '</tbody>
    				    <tfoot>
    						<tr>
    							<th><input type="checkbox" class="select_all" /></th>
    							<th class="orderitem '.$idClass.'" order="id">
    							    <div class="sorting"><span>ID</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$license_keyClass.'" order="license_key">
    							    <div class="sorting"><span>License Key</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$activatedClass.'" order="activated">
    							    <div class="sorting"><span>Activated</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$activation_dateClass.'" order="activation_date">
    							    <div class="sorting"><span>Activation Date</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th class="orderitem '.$disabledClass.'" order="disabled">
    							    <div class="sorting"><span>Status</span><span class="sorting-indicator"></span></div>
    							</th>
    							<th><a href="#" class="ik_armember_button_delete_selected button action">Remove</a></th>
    						</tr>
    					</tfoot>
    					<tbody>
    				</table>';
		    
    
        	// If licenses filtered exist
        	if (isset($licenses[0]->id)){
        	    
        	    //I create value for paging purposes
        	    $listing_total = count($licenses_all);
    	
    			$licenses_list = $table_head;
        		
        	    foreach ($licenses as $license){
        	            
        	            //If activated value = 0 is inactive, 1 is active
        	            if ($license->activated == 0){
        	                $license_status = 'Inactive';
        	            } else {
        	                $license_status = 'Active';        	                
        	            }
        	            
        	            //If disabled value = 0 is inactive, 1 is active
        	            if ($license->disabled == 0){
        	                $license_status_action = 'Disable';
        	                $license_status_disabled = 'Enabled';
        	                $license_status_action_status = '1';
        	            } else {
        	                $license_status_action = 'Enable';
        	                $license_status_disabled = 'Disabled';
        	                $license_status_action_status = '0';
        	            }

        	            
        	            if ($license->activation_date == '0000-00-00 00:00:00'){
        	                $license->activation_date = '-';
        	            }
        	            
        				$licenses_list.= '
        				<tr iddata="'.$license->id.'">
        					<td><input type="checkbox" class="select_data" /></td>
        					<td class="license_id">'.$license->id.'</td>
        					<td class="license_key">'.$license->license_key.'</td>
        					<td class="activated">'.$license_status.'</td>
        					<td class="date-activated">'.$license->activation_date.'</td>
        					<td class="status">'.$license_status_disabled.'</td>
        					<td iddata="'.$license->id.'">
        						<button class="ik_armember_button_edit_license button action">More Info</button>
        						<button class="ik_armember_button_activation_license button action" status="'.$license_status_action_status.'">'.$license_status_action.'</button>
        						<button class="ik_armember_button_delete_license button action">Remove</button></td>
        				</tr>';			
        	    }
        	    
    			$licenses_list.= $table_foot;
    				
    			if ($page > 0){
        			$total_pages = intval($listing_total / $qty);
        			
                    if ($listing_total > $qty && $page <= $total_pages){
                        $licenses_list.= '<div class="ik_armember_pages">';
                        
                        //If there are a lot of pages
                        if ($total_pages > 11){
                            $almostlastpage1 = $total_pages - 1;
                            $almostlastpage2 = $total_pages - 2;
                            $halfpages1 = intval($total_pages/2);
                            $halfpages2 = intval($total_pages/2)-1;
                            
                            $listing_limit = array('1', '2', $page, $halfpages2, $halfpages1, $almostlastpage2, $almostlastpage1, $total_pages);
                            
                            $pages_limited = true;
                        } else{
                            $listing_limit[0] = false;
                            $pages_limited = false;
                        }
                        $arrowprevious = $page - 1;
                        $arrownext = $page + 1;
                        if ($arrowprevious > 1){
                            $licenses_list.= '<a href="'.$url_licensesadmin_unlisted.'&listing='.$arrowprevious.'"><</a>';
                        }
                        for ($i = 1; $i <= $total_pages; $i++) {
                            $showpage = true;
                            
                            if ($pages_limited == true && !in_array($i, $listing_limit)){
                                $nextpage = $page+1;
                                $beforepage = $page - 1;
                                if ($page != $i && $nextpage != $i && $beforepage != $i){
                                    $showpage = false;
                                }
                            }
                            
                            if ($showpage == true){
                                if ($page == $i){
                                    $selectedPageN = 'class="actual_page"';
                                } else {
                                    $selectedPageN = "";
                                }
                                
                                $licenses_list.= '<a '.$selectedPageN.' href="'.$url_licensesadmin_unlisted.'&listing='.$i.'">'.$i.'</a>';
                                
                            }
                            
                        }
                        if ($arrownext < $total_pages){
                            $licenses_list.= '<a href="'.$url_licensesadmin_unlisted.'&listing='.$arrownext.'">></a>';
                        }
                        $licenses_list.= '</div>';
                	}
    			}

        	    return $licenses_list;
        	} else {
                echo $table_head.'<tr id="ik_armember_editor_dyn_data" class="ik_armember_editor_data"><td colspan="7"><p>Nothing Found</p>
                    <a class="button-primary" href="'.$url_licensesadmin_unfiltered.'">Show All</a></td></tr>'.$table_foot;
        	}
        	    
        }
        
        return false;
    
    }
}
?>