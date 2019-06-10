<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_map
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;


$doc = JFactory::getDocument();

$module_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(__DIR__));

// Add Leaflet assets:
$doc->addStyleSheet($module_path . '/assets/leaflet/leaflet.css', null, array('integrity' => 'sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ==', 'crossorigin' => '__EMPTY_STRING__'));
$doc->addScript($module_path . '/assets/leaflet/leaflet.js', null, array('integrity' => 'sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw==', 'crossorigin' => '__EMPTY_STRING__'));

$doc->addStyleSheet($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.css');
$doc->addScript($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.js');

$doc->addScript($module_path . '/assets/leaflet-svgicon/leaflet-svg-icon.js');

$doc->addScript($module_path . '/assets/leaflet-map.js');


$lat   = $params->get('lat');
$lng   = $params->get('lng');
$zoom  = $params->get('zoom');
$token = $params->get('access_token');


$markers_json = 'null';
$manual_markers = $params->get('manual_markers', false);
/*
if ($manual_markers) {
    $markers = array();
    
    // Treat markers as CSV.    
    $markers_data = ModBrochureHelper::csvArray($manual_markers);
    foreach ($markers_data as $row) {
        $markers[] = array_combine(array('lat', 'lng', 'color', 'popup'), $row);
    }
    
    $markers_json = json_encode($markers);
}
*/
$map_data = new StdClass();
$map_data->lat   = $lat;        
$map_data->lng   = $lng;        
$map_data->zoom  = $zoom;       
$map_data->token = $token;      

$static_map_src = 'https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/' . $lng  . ',' . $lat  . ',' . $zoom . ',0,0/600x600?access_token=' . $token;

$map_id = 'brochure_map_' . $module->id;

?>
<div class="brochure__content--map">
    <img src="<?php echo $static_map_src; ?>" alt="" id="<?php echo $map_id; ?>">
    <p>No javascript available, can't display an interactive map.
</div>

<script>
leafletMapInitialize('<?php echo $map_id; ?>', <?php echo json_encode($map_data); ?>, <?php echo $markers_json; ?>);
</script>