/* 
Armember to Mapmarker - Main JS
Created: 06/12/2021
Last Update: 08/12/2021
Author: Gabriel Caroprese
*/

jQuery(document).ready(function ($) {
    jQuery('#ik_armember_map_add_licenses').on('click', '#ik_armember_add_field', function(){
        jQuery('#ik_armember_map_add_licenses .ik_armember_license_fields ul').append('<li><input type="text" required name="license_key[]" placeholder="License Key" /> <a href="#" class="ik_armember_delete_field button">Delete</a></li>');
        return false;
    });
    
    jQuery('#ik_armember_map_add_licenses').on('click', '.ik_armember_delete_field', function(){
        jQuery(this).parent().remove();
        return false;
    });
    
    jQuery('#ik_armember_map_add_licenses').on('click', '#ik_armember_map_add_licenses .ik_armember_license_fields .ik_armember_delete_field_creado', function(){
        var confirmar =confirm('Confirmar deleting license key?');
        var element_delete = jQuery(this).parent();
        if (confirmar == true) {
         
            var iddata = parseInt(jQuery(this).attr('iddata'));
            if (iddata != 0){
                
     			var data = {
    				action: "ik_armember_ajax_delete_license",
    				"post_type": "post",
    				"iddata": iddata,
    			};  
    
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
                        element_delete.fadeOut(700);
                        element_delete.remove();
        		    }        
                });
            }
        }
        return false;
    });
    
    jQuery("#ik_armember_existing_licenses th .select_all").on( "click", function() {
        if (jQuery(this).attr('seleccionado') != 'si'){
            jQuery('#ik_armember_existing_licenses th .select_all').prop('checked', true);
            jQuery('#ik_armember_existing_licenses th .select_all').attr('checked', 'checked');
            jQuery('#ik_armember_existing_licenses tbody tr').each(function() {
                jQuery(this).find('.select_data').prop('checked', true);
                jQuery(this).find('.select_data').attr('checked', 'checked');
            });        
            jQuery(this).attr('seleccionado', 'si');
        } else {
            jQuery('#ik_armember_existing_licenses th .select_all').prop('checked', false);
            jQuery('#ik_armember_existing_licenses th .select_all').removeAttr('checked');
            jQuery('#ik_armember_existing_licenses tbody tr').each(function() {
                jQuery(this).find('.select_data').prop('checked', false);
                jQuery(this).find('.select_data').removeAttr('checked');
            });   
            jQuery(this).attr('seleccionado', 'no');
            
        }
    });
    
    jQuery("#ik_armember_existing_licenses .ik_armember_button_delete_selected").on( "click", function() {
        jQuery('#ik_armember_existing_licenses tbody tr').each(function() {
            var element_delete = jQuery(this).parent();
            if (jQuery(this).find('.select_data').prop('checked') == true){
                
                var license_tr = jQuery(this);
                var iddata = license_tr.attr('iddata');
                
                var data = {
    				action: "ik_armember_ajax_delete_license",
    				"post_type": "post",
    				"iddata": iddata,
    			};  
    
        		jQuery.post( ajaxurl, data, function(response) {
        			if (response){
                        license_tr.fadeOut(700);
                        license_tr.remove();
        		    }        
                });
            }
        });  
        jQuery('#ik_armember_existing_licenses th .select_all').attr('seleccionado', 'no');
        jQuery('#ik_armember_existing_licenses th .select_all').prop('checked', false);
        jQuery('#ik_armember_existing_licenses th .select_all').removeAttr('checked');
        return false;
    });

    jQuery('#ik_armember_existing_licenses').on('click','td .ik_armember_button_delete_license', function(e){
        e.preventDefault();
        var confirmar =confirm('Confirm deleting license key?');
        if (confirmar == true) {
            var iddata = jQuery(this).parent().attr('iddata');
            var license_tr = jQuery('#ik_armember_existing_licenses tbody').find('tr[iddata='+iddata+']');
            
            var data = {
    			action: "ik_armember_ajax_delete_license",
    			"post_type": "post",
    			"iddata": iddata,
    		};  
    
    		jQuery.post( ajaxurl, data, function(response) {
    			if (response){
                    license_tr.fadeOut(700);
                    license_tr.remove();
                    jQuery('#ik_armember_editor_dyn_data').remove();
    		    }        
            });
        }
    });

	jQuery('#ik_armember_existing_licenses').on('click','#ik_armember_button_cancel_dynamic_editor', function(e){
        e.preventDefault();
		jQuery('#ik_armember_editor_dyn_data').remove();
	});
	
    jQuery('#ik_armember_existing_licenses').on('click','td .ik_armember_button_edit_license', function(e){
        e.preventDefault();
        jQuery(this).prop('disabled', true);
        jQuery('#ik_armember_existing_licenses .ik_armember_editor_data').remove();
        var iddata = jQuery(this).parent().attr('iddata');
        var buttonclicked = jQuery(this);
        var license_tr = jQuery('#ik_armember_existing_licenses tbody').find('tr[iddata='+iddata+']');

        var data = {
			action: "ik_armember_ajax_edit_license",
			"post_type": "post",
			"iddata": iddata,
		};  
		
		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
                license_tr.after('<tr id="ik_armember_editor_dyn_data" class="ik_armember_editor_data"><td colspan="7"><div>'+data+'<a href="#" class="button button-primary" id="ik_armember_button_cancel_dynamic_editor" style="margin-left: 5px;">Close</a></div></td></tr>');
                buttonclicked.prop('disabled', false);
    	    }        
        });
    
    });

    jQuery('#ik_armember_existing_licenses').on('click','.ik_armember_button_activation_license', function(e){
        e.preventDefault();
        jQuery(this).prop('disabled', true);
        var iddata = jQuery(this).parent().attr('iddata');
        var buttonclicked = jQuery(this);
        var statusid = jQuery(this).attr('status');

        var data = {
			action: "ik_armember_ajax_disable_enable_license",
			"post_type": "post",
			"iddata": iddata,
			"statusid": statusid,
		};  
		
		jQuery.post( ajaxurl, data, function(response) {
			if (response){
			    var data = JSON.parse(response);
                buttonclicked.text(data.text);
                buttonclicked.attr('status', data.value);
                buttonclicked.prop('disabled', false);
    	    }        
        });
    
    });

    jQuery('#ik_armember_existing_licenses table').on('click','.orderitem', function(e){
        e.preventDefault();
        
        var order = jQuery(this).attr('order');
        var urlnow = window.location.href;
        
        if (order != undefined){
            if (jQuery(this).hasClass('desc')){
                var direc = 'asc';
            } else {
                var direc = 'desc';
            }
                
                
            if (order == 'license_key'){
                var order_request = '&order=license_key&orderdir='+direc;
                window.location.href = urlnow+order_request;
            } else if (order == 'activation_date'){
                var order_request = '&order=activation_date&orderdir='+direc;
                window.location.href = urlnow+order_request;
            } else if (order == 'id'){
                var order_request = '&order=id&orderdir='+direc;
                window.location.href = urlnow+order_request;
            } else if (order == 'user_activated'){
                var order_request = '&order=user_activated&orderdir='+direc;
                window.location.href = urlnow+order_request;
            } else if (order == 'activated'){
                var order_request = '&order=activated&orderdir='+direc;
                window.location.href = urlnow+order_request;
            } else {
                var order_request = '&order=disbled&orderdir='+direc;
                window.location.href = urlnow+order_request;
            }
        }
    });   
});