jQuery(document).ready(function ($) {
    jQuery('body').on('click', '#armember_map_geolocalize', function(event){
          event.preventDefault();
    
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(displayCoords);
        } else { 
            alert('Geolocation is not supported by this browser.');
        }
        
        return false;

    });
    
    jQuery('.arm_member_form_container').on('click', '#armember_map_test_url', function(event){
        event.preventDefault();
        
        var urltotest = jQuery('.arm_member_form_container input[name=url_reference]').val();
        
        if (urltotest != undefined && urltotest != ''){
              var regex = /(?:https?):\/\/(\w+:?\w*)?(\S+)(:\d+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/;
              if(!regex .test(urltotest)) {
                alert("Please, enter valid URL.");
              } else {
                window.open(urltotest, '_blank');;
              }
        }
        
        return false;

    });

    jQuery('.arm_member_form_container .armFileUploadWrapper input.arm_file_url').each(function() {
        var filepath = jQuery(this).val();
        jQuery(this).parent().find('.armNormalFileUpload img').wrap('<a href="'+filepath+'" target="_blank"></a>');
    });
    
    var filepath = jQuery(this).val();
    jQuery(this).parent().find('.armNormalFileUpload img').wrap('<a href="'+filepath+'" target="_blank"></a>');
    
    jQuery('.arm_member_form_container .armFileUploadWrapper input.arm_file_url').on('change',function ()
    {
        var fileinput = jQuery(this);
        var filepath = fileinput.val();
        
		var data = {
			action: "ik_armember_ajax_update_filename",
			"post_type": "post",
			"filepath": filepath,
		};  

		jQuery.post( ik_armember_ajaxurl.ajaxurl, data, function(response) {
			if (response){			
                var newfilepath = filepath.replace("armFile", "CubeMenu");
        
                fileinput.val(newfilepath);
                fileinput.parent().find('.armNormalFileUpload img').wrap('<a href="'+newfilepath+'" target="_blank"></a>');
                console.log('File uploaded.');
			}
		}, "json");    
        
        
    });
        
    jQuery('#armember_map_geolocalize').attr('style', 'cursor: pointer');
    jQuery('body').on('focus', '.arm_form_inner_container input[name=longitude]', function(event){
        jQuery('.arm_form_inner_container input[name=longitude]').trigger( "blur" )
        jQuery('.arm_form_inner_container input[name=longitude]').attr('style', 'pointer-events: none;');
    });
    jQuery('body').on('focus', '.arm_form_inner_container input[name=latitude]', function(event){
        jQuery('.arm_form_inner_container input[name=latitude]').trigger( "blur" )
        jQuery('.arm_form_inner_container input[name=latitude]').attr('style', 'pointer-events: none;');
    });
    
    jQuery('.arm_form_inner_container input[name=latitude]').attr('style', 'pointer-events: none;');
    jQuery('.arm_form_inner_container input[name=longitude]').attr('style', 'pointer-events: none;');
    
    function displayCoords(position) {
        jQuery('.arm_form_inner_container input[name=latitude]').val(position.coords.latitude);
        jQuery('.arm_form_inner_container input[name=latitude]').attr('value', position.coords.latitude);
        jQuery('.arm_form_inner_container input[name=longitude]').val(position.coords.longitude);
        jQuery('.arm_form_inner_container input[name=longitude]').attr('value', position.coords.longitude);
        jQuery('.arm_form_inner_container input[name=latitude]').trigger('keydown');
        jQuery('.arm_form_inner_container input[name=latitude]').trigger('change');
        jQuery('.arm_form_inner_container input[name=longitude]').trigger('keydown');
        jQuery('.arm_form_inner_container input[name=longitude]').trigger('change');
        jQuery('.arm_form_inner_container input[name=latitude]').trigger('focus');
        jQuery('.arm_form_inner_container input[name=longitude]').trigger('focus');
    }
});