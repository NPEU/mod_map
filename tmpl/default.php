<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_map
 *
 * @copyright   Copyright (C) NPEU 2019.
 * @license     MIT License; see LICENSE.md
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$doc = Factory::getDocument();



$map_id = 'map_' . $module->id;
$lat    = $params->get('lat');
$lng    = $params->get('lng');
$zoom   = $params->get('zoom');
$token  = $params->get('access_token');
$height = $params->get('height');
$legend = $params->get('legend');

$doc->addStyleDeclaration('#' . $map_id . ' {min-height: ' . $height . 'px;}');

$markers = array_merge($manual_markers, $remote_markers);
$markers_json   = 'null';

if (!empty($markers)) {
    $markers_json = json_encode($markers);
}

$map_data = new StdClass();
$map_data->lat   = $lat;
$map_data->lng   = $lng;
$map_data->zoom  = $zoom;
$map_data->token = $token;

$static_map_alt   = $params->get('static_map_alt', '');
$static_map_no_js = $params->get('static_map_no_js', '');
$static_map_src   = 'https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/' . $lng  . ',' . $lat  . ',' . $zoom . ',0,0/600x600?access_token=' . $token;
// https://api.mapbox.com/styles/v1/mapbox/streets-v10/static/pin-s+FD7567(-1.217606,51.763051),pin-s+FD7567(-1.82997,52.48022),pin-s+FD7567(-0.88661,52.237229),pin-s+FD7567(-1.331119,51.060495),pin-s+FD7567(-1.434935,50.935252),pin-s+FD7567(-2.590499,51.495281),pin-s+FD7567(-1.135465,52.628261),pin-s+FD7567(-0.958653,51.451504),pin-s+FD7567(-0.526844,51.377158),pin-s+FD7567(-1.796112,53.806935),pin-s+FD7567(0.898902,51.90998),pin-s+FD7567(-1.72674,51.538524),pin-s+FD7567(0.541576,51.379792),pin-s+FD7567(1.220473,52.61729),pin-s+FD7567(-4.115701,50.415988),pin-s+FD7567(-2.122233,53.553588)/-2,53.5,5/600x600?access_token=pk.eyJ1IjoiYW5keWtraXJrIiwiYSI6ImNqbGh3a3FnbzA1aDMza204eDJnMmVhMmMifQ.I7diR0BZvQWzn2okKy6qIQ

?>
<div id="<?php echo $map_id; ?>">
    <img src="<?php echo $static_map_src; ?>" alt="<?php echo $static_map_alt; ?>">
    <?php if (!empty($static_map_no_js)): ?>
    <p>
        <?php echo $static_map_no_js; ?>
    </p>
    <?php endif; ?>
</div>
<?php if (!empty($legend)): ?>
<div>
    <?php echo $legend; ?>
</div>
<?php endif; ?>
<script>
leafletMapInitialize('<?php echo $map_id; ?>', <?php echo json_encode($map_data); ?>, <?php echo $markers_json; ?>);
</script>