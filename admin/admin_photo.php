<?php
/***********************************************
* File      :   admin_photo.php
* Project   :   piwigo-openstreetmap
* Descr     :   Location edit in photo panel
*
* Created   :   2.11.2013
*
* Copyright 2012-2013 <xbgmsharp@gmail.com>
*
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
************************************************/

// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Check access and exit when user status is not ok
check_status(ACCESS_ADMINISTRATOR);

if (!isset($_GET['image_id']) or !isset($_GET['section']))
{
	die('Invalid data!');
}

check_input_parameter('image_id', $_GET, false, PATTERN_ID);

$admin_photo_base_url = get_root_url().'admin.php?page=photo-'.$_GET['image_id'];
$self_url = get_root_url().'admin.php?page=plugin&amp;section=piwigo-openstreetmap/admin/admin_photo.php&amp;image_id='.$_GET['image_id'];

load_language('plugin.lang', PHPWG_PLUGINS_PATH.basename(dirname(__FILE__)).'/');
load_language('plugin.lang', OSM_PATH);

global $template, $page, $conf;

if (isset($_POST['submit']))
{
	check_pwg_token();

	$lat = trim($_POST['osmlat']);
	$lon = trim($_POST['osmlon']);
	if ( strlen($lat)>0 and strlen($lon)>0 )
	{
		if ( (double)$lat<=90 and (double)$lat>=-90
			and (double)$lon<=180 and (double)$lon>=-180 )
			$update_query = 'lat='.$lat.', lon='.$lon;
		else
			$page['errors'][] = 'Invalid lat or lon value';
	}
	elseif ( strlen($lat)==0 and strlen($lon)==0 )
		$update_query = 'lat=NULL, lon=NULL';
	else
		$page['errors'][] = 'Both lat/lon must be empty or not empty';

	if (isset($update_query))
	{
		$update_query = 'UPDATE '.IMAGES_TABLE.' SET '.$update_query.' WHERE id = '.$_GET['image_id'].';';
		pwg_query($update_query);
	}

	if (count($page['errors']) == 0 )
	{
		array_push( $page['infos'], l10n('The photo was updated'));
	}
}

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');
$tabsheet = new tabsheet();
$tabsheet->set_id('photo');
$tabsheet->select('openstreetmap');
$tabsheet->assign();

$template->set_filenames(
  array(
    'plugin_admin_content' => dirname(__FILE__).'/admin_photo.tpl'
    )
  );
 
// Retrieving direct information about picture
$query = 'SELECT * FROM '.IMAGES_TABLE.' WHERE id = '.$_GET['image_id'].';';
$picture = pwg_db_fetch_assoc(pwg_query($query));
$lat = isset($picture['lat']) ? $picture['lat'] : 0;
$lon = isset($picture['lon']) ? $picture['lon'] : 0;

// Load parameter, fallback to default if unset
$zoom = isset($conf['osm_conf']['right_panel']['zoom']) ? $conf['osm_conf']['right_panel']['zoom'] : '18';
$baselayer = isset($conf['osm_conf']['map']['baselayer']) ? $conf['osm_conf']['map']['baselayer'] : 'mapnik';
$custombaselayer = isset($conf['osm_conf']['map']['custombaselayer']) ? $conf['osm_conf']['map']['custombaselayer'] : '';
$custombaselayerurl = isset($conf['osm_conf']['map']['custombaselayerurl']) ? $conf['osm_conf']['map']['custombaselayerurl'] : '';
$noworldwarp = isset($conf['osm_conf']['map']['noworldwarp']) ? $conf['osm_conf']['map']['noworldwarp'] : 'false';
$attrleaflet = isset($conf['osm_conf']['map']['attrleaflet']) ? $conf['osm_conf']['map']['attrleaflet'] : 'false';
$attrimagery = isset($conf['osm_conf']['map']['attrimagery']) ? $conf['osm_conf']['map']['attrimagery'] : 'false';
$attrmodule = isset($conf['osm_conf']['map']['attrplugin']) ? $conf['osm_conf']['map']['attrplugin'] : 'false';

// Load baselayerURL
// Key1 BC9A493B41014CAABB98F0471D759707
if     ($baselayer == 'mapnik')		$baselayerurl = 'http://tile.openstreetmap.org/{z}/{x}/{y}.png';
else if($baselayer == 'mapquest')	$baselayerurl = 'http://otile1.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
else if($baselayer == 'cloudmade')	$baselayerurl = 'http://{s}.tile.cloudmade.com/7807cc60c1354628aab5156cfc1d4b3b/997/256/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikde')	$baselayerurl = 'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikfr')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png';
else if($baselayer == 'blackandwhite')	$baselayerurl = 'http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikhot')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
else if($baselayer == 'mapquestaerial')	$baselayerurl = 'http://oatile1.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.jpg';
else if($baselayer == 'custom')	$baselayerurl = $custombaselayerurl;

$attribution = osmcopyright($attrleaflet, $attrimagery, $attrmodule, $baselayer, $custombaselayer);

if ($lat == 0 and $lon == 0) { $zoom = 2; }

// Generate Javascript
// Override configuration due to the use of leaflet-providers.js
// ----------------------------------------
$js = "\nvar map = L.map('map', { 'zoomControl': false, }).setView([".$lat.", ".$lon."], ".$zoom.");

L.tileLayer('".$baselayerurl."', { maxZoom: 18 }).addTo(map);

map.addControl(L.control.zoom({position: 'topright'}));

L.marker([".$lat.", ".$lon."]).addTo(map)
	.bindPopup('".render_element_name($picture)."').openPopup();

/* BEGIN leaflet-providers */
var baseLayers = ['OpenStreetMap.Mapnik', 'OpenStreetMap.BlackAndWhite', 'OpenStreetMap.DE', 'OpenStreetMap.HOT', 'MapQuestOpen.OSM', 'MapQuestOpen.Aerial', 'Stamen.Watercolor'],
    overlays = [''];

L.control.layers.provided(baseLayers, overlays).addTo(map);
/* END leaflet-providers */
\n";

$template->assign(array(
	'PWG_TOKEN' => get_pwg_token(),
	'F_ACTION' => $self_url,
	'TN_SRC' => DerivativeImage::thumb_url($picture).'?'.time(),
	'TITLE' => render_element_name($picture),
	'OSM_PATH' => OSM_PATH,
	'OSM_JS' => $js,
	'LAT' => $lat,
	'LON' => $lon,
));

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
