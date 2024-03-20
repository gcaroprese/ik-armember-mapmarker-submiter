<?php

/* 
Armember to Mapmarker - Config Page
Created: 17/11/2021
Last Update: 29/12/2021
Author: Gabriel Caroprese
*/

if ( ! defined('ABSPATH')) exit('restricted access');
?>

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    if (isset($_POST['ik_armember_map_form_id'])){
        $form_id_submited = absint($_POST['ik_armember_map_form_id']);
        update_option('ik_armember_map_form_id', $form_id_submited);
    }
    if (isset($_POST['ik_armember_map_map_id'])){
        $map_id_submited = absint($_POST['ik_armember_map_map_id']);
        update_option('ik_armember_map_map_id', $map_id_submited);
    }
    if (isset($_POST['ik_armember_map_map_icon'])){
        $map_icon_submited = sanitize_text_field($_POST['ik_armember_map_map_icon']);
        update_option('ik_armember_map_map_icon', $map_icon_submited);
    }
}
  
// Check the value of the form saved
$form_id_saved = get_option('ik_armember_map_form_id');
if ($form_id_saved == false || $form_id_saved == NULL ){
    $form_id_saved = 0;
}
$map_id_saved = get_option('ik_armember_map_map_id');
if ($map_id_saved == false || $map_id_saved == NULL ){
    $map_id_saved = 0;
}
$map_icon_saved = get_option('ik_armember_map_map_icon');
if ($map_icon_saved == false || $map_icon_saved == NULL ){
    $map_icon_saved = 0;
}

$form_ids_option_list = ik_armember_map_forms_option_list();
$map_ids_option_list = ik_armember_map_id_option_list();
$map_icons = ik_armember_map_icons_list();

?>

<style>
.error, .updated, #setting-error-tgmpa{display: none! important;}
</style>
<div id="ik_armember_map_config">
    <h1>CubeMenu Markers</h1>
    <form action="" method="post" id="ik_armember_map_config_form" enctype="multipart/form-data" autocomplete="no">
        <p>
            <label>
    			<span>Select ARMember Contact form to import data to map marker</span><br />
    			<select required id="ik_armember_map_form_id" name="ik_armember_map_form_id">
    			    <?php echo $form_ids_option_list; ?>
    			</select>
            </label>
        </p>
        <p>
             <label>
    			<span>Select Map ID to be assigned</span><br />
    			<select required id="ik_armember_map_map_id" name="ik_armember_map_map_id">
    			    <?php echo $map_ids_option_list; ?>
    			</select>
            </label>
        </p>
        <p>
             <label>
    			<span>Select Map Marker Icon to be assigned</span><br />
    			<select required id="ik_armember_map_map_icon" name="ik_armember_map_map_icon">
    			    <?php echo $map_icons; ?>
    			</select>
            </label>
        </p>
        <p>
            <input type="submit" value="Save" class="button-primary">
        </p>
    </form>
    <h3>Metakey Names:</h3>
    <p>
        <b>Name:</b> Metakey text "business_name"<br />
        <b>Latitude:</b> Metakey text "latitude"<br />
        <b>Longitude:</b> Metakey text "longitude"<br />
        <b>URL:</b> Metakey text "url_reference"<br />
        <b>File:</b> Metakey file "file_reference"<br />
        <b>Geolocalize:</b> Use an HTML field and add a custom link code with the id "armember_map_geolocalize" such as <br />
        <code>&lt;a id="armember_map_geolocalize" class="et_pb_button et_pb_more_button et_pb_button_one"&gt;Geolocalize me&lt;/a&gt;</code><br />
        <b>Test URL:</b> Use an HTML field and add a custom link code with the id "armember_map_test_url" such as <br />
        <code>&lt;a id="armember_map_test_url" class="et_pb_button et_pb_more_button et_pb_button_one"&gt;Test URL&lt;/a&gt;</code><br />
        <b>Delete Profile:</b> Use an HTML field and add the shortcode [armember_delete_user]
    </p>
    <script>
        jQuery('#ik_armember_map_form_id').val('<?php echo $form_id_saved; ?>');
        jQuery('#ik_armember_map_map_id').val('<?php echo $map_id_saved; ?>');
        jQuery('#ik_armember_map_map_icon').val('<?php echo $map_icon_saved; ?>');
    </script>
</div>