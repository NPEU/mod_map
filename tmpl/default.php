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
$doc->addStyleSheet($module_path . '/assets/leaflet/leaflet.css');
$doc->addScript($module_path . '/assets/leaflet/leaflet.js');

$doc->addStyleSheet($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.css');
$doc->addScript($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.js');

$doc->addScript($module_path . '/assets/leaflet-svgicon/leaflet-svg-icon.js');

$doc->addScript($module_path . '/assets/leaflet-map.js');


$lat    = $params->get('lat');
$lng    = $params->get('lng');
$zoom   = $params->get('zoom');
$token  = $params->get('access_token');
$height = $params->get('height');


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
// https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/pin-s+FD7567(-1.217606,51.763051),pin-s+FD7567(-1.82997,52.48022),pin-s+FD7567(-0.88661,52.237229),pin-s+FD7567(-1.331119,51.060495),pin-s+FD7567(-1.434935,50.935252),pin-s+FD7567(-2.590499,51.495281),pin-s+FD7567(-1.135465,52.628261),pin-s+FD7567(-0.958653,51.451504),pin-s+FD7567(-0.526844,51.377158),pin-s+FD7567(-1.796112,53.806935),pin-s+FD7567(0.898902,51.90998),pin-s+FD7567(-1.72674,51.538524),pin-s+FD7567(0.541576,51.379792),pin-s+FD7567(1.220473,52.61729),pin-s+FD7567(-4.115701,50.415988),pin-s+FD7567(-2.122233,53.553588)/-2,53.5,5/600x600?access_token=pk.eyJ1IjoiYW5keWtraXJrIiwiYSI6ImNqbGh3a3FnbzA1aDMza204eDJnMmVhMmMifQ.I7diR0BZvQWzn2okKy6qIQ

$map_id = 'map_' . $module->id;

?>
<figure>
    <div class="c-map  c-map--<?php echo $height; ?>  u-space--below" id="<?php echo $map_id; ?>">
        <p class="u-text-align--center">
            <img class="c-map__static" src="<?php echo $static_map_src; ?>" alt="">
        </p>
        <p class="u-text-align--center">No javascript available, can't display an interactive map.</p>
    </div>
    <figcaption>
        <span class="u-text-group  u-text-group--center  u-text-group--wide-space">
            <span><img alt="Red marker" class="icon--marker" height="32" src="https://www.npeu.ox.ac.uk/img/icons/red-marker.svg" width="21"> - Recruiting site</span>
        </span>
    </figcaption>
</figure>
<script>
leafletMapInitialize('<?php echo $map_id; ?>', <?php echo json_encode($map_data); ?>, <?php echo $markers_json; ?>);
</script>