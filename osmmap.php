<?php
/***********************************************
* File      :   maintain.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display a world map
*
* Created   :   28.05.2013
*
* Copyright 2013 <xbgmsharp@gmail.com>
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

if ( !defined('PHPWG_ROOT_PATH') )
  define('PHPWG_ROOT_PATH','../../');

include_once( PHPWG_ROOT_PATH.'include/common.inc.php' );
include_once( dirname(__FILE__) .'/include/functions.php');
include_once( dirname(__FILE__) .'/include/functions_map.php');

$osm_dir = "piwigo-openstreetmap";

check_status(ACCESS_GUEST);
//if (!isset($osm_dir))
//  access_denied( 'Plugin not installed' );

osm_load_language();

$section = '';
if ( $conf['question_mark_in_urls']==false and isset($_SERVER["PATH_INFO"]) and !empty($_SERVER["PATH_INFO"]) )
{
	$section = $_SERVER["PATH_INFO"];
	$section = str_replace('//', '/', $section);
	$path_count = count( explode('/', $section) );
	$page['root_path'] = PHPWG_ROOT_PATH.str_repeat('../', $path_count-1);
	if ( strncmp($page['root_path'], './', 2) == 0 )
	{
		$page['root_path'] = substr($page['root_path'], 2);
	}
}
else
{
	foreach ($_GET as $key=>$value)
	{
		if (!strlen($value)) $section=$key;
		break;
	}
}

// deleting first "/" if displayed
$tokens = explode('/', preg_replace('#^/#', '', $section));
$next_token = 0;
$result = osm_parse_map_data_url($tokens, $next_token);
$page = array_merge( $page, $result );

if (isset($page['category']))
	check_restrictions($page['category']['id']);

// Fetch data lat and lon
$query="SELECT `lat`, `lon`, `file`,`path` FROM ".IMAGES_TABLE." WHERE `lat` IS NOT NULL AND `lon` IS NOT NULL;";
$php_data = array_from_query($query);
//print_r($php_data);
$js_data = array();
foreach($php_data as $array)
{
	$extension_pos = strrpos($array['path'], '.');
	$thumb = substr($array['path'], 0, $extension_pos) . '-sq' . substr($array['path'], $extension_pos);
	$js_data[] = array((double)$array['lat'], (double)$array['lon'], $array['file'], $thumb);
}
//echo json_encode($js_data);

// Load parameter, fallback to default if unset
$baselayer = isset($conf['osm_baselayer']) ? $conf['osm_baselayer'] : 'mapnik';
$custombaselayer = isset($conf['$custombaselayer']) ? $conf['$custombaselayer'] : '';
$custombaselayerurl = isset($conf['$custombaselayerurl']) ? $conf['$custombaselayerurl'] : '';
$noworldwarp = isset($conf['osm_noworldwarp']) ? $conf['osm_noworldwarp'] : 'false';
$attrleaflet = isset($conf['osm_attrleaflet']) ? $conf['osm_attrleaflet'] : 'false';
$attrimagery = isset($conf['osm_attrimagery']) ? $conf['osm_attrimagery'] : 'false';
$attrmodule = isset($conf['osm_attrmodule']) ? $conf['osm_attrmodule'] : 'false';

$IMAGERY="Imagery by";
$PLG_BY="Plugin by";
$OSMCOPYRIGHT='Map data © <a href="http://www.openstreetmap.org" target="_blank">OpenStreetMap</a> (<a href="http://www.openstreetmap.org/copyright" target="_blank">ODbL</a>)';

// Load baselayerURL
// Key1 BC9A493B41014CAABB98F0471D759707
if     ($baselayer == 'mapnik')		$baselayerurl = 'http://tile.openstreetmap.org/{z}/{x}/{y}.png';
else if($baselayer == 'mapquest')	$baselayerurl = 'http://otile1.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
else if($baselayer == 'cloudmade')	$baselayerurl = 'http://{s}.tile.cloudmade.com/7807cc60c1354628aab5156cfc1d4b3b/997/256/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikde')	$baselayerurl = 'http://www.toolserver.org/tiles/germany/{z}/{x}/{y}.png';
else if($baselayer == 'custom')		$baselayerurl = $custombaselayerurl;

// Generate Javascript
// ----------------------------------------
// no worldWarp (no world copies, restrict the view to one world)
if($noworldwarp)
{
	$nowarp = "noWrap: true, ";
	$worldcopyjump = "worldCopyJump: false, maxBounds: [ [82, -180], [-82, 180] ]";
}
else
{
	$nowarp = "noWrap: false, ";
	$worldcopyjump = "worldCopyJump: true";
}

$js = "\nvar addressPoints = ". json_encode($js_data, JSON_UNESCAPED_SLASHES) .";\n";

// Create the map and get a new map instance attached and element with id="tile-map"
$js .= "\nvar Url = '".$baselayerurl."',
	Attribution = '".$OSMCOPYRIGHT."',
	TileLayer = new L.TileLayer(Url, {maxZoom: 18, attribution: Attribution}),
	latlng = new L.LatLng(0, 0);\n";
$js .= "var map = new L.Map('map', {center: latlng, zoom: 2, layers: [TileLayer]});\n";
$js .= "map.attributionControl.setPrefix('');\n";
$js .= "var markers = new L.MarkerClusterGroup();\n";
$js .= "for (var i = 0; i < addressPoints.length; i++) {
	var a = addressPoints[i];
	var title = a[2];
	var imgurl = '". get_absolute_root_url() ."i.php?'+a[3];
	var latlng = new L.LatLng(a[0], a[1]);
	var marker = new L.Marker(latlng, { title: title });
	marker.bindPopup('<p>'+title+'<br /><img src=\"'+imgurl+'\"></p>').openPopup();
	markers.addLayer(marker);
}";
$js .= "map.addLayer(markers);\n";

// Attribution Credit and Copyright
if($attrleaflet){ $js .= "map.attributionControl.addAttribution('Powered by Leaflet');\n"; }
if($attrimagery){ $js .= "map.attributionControl.addAttribution('".$IMAGERY." ". imagery($baselayer, $custombaselayer)."');\n"; }
if($attrmodule){ $js .= "map.attributionControl.addAttribution('".$PLG_BY." <a href=\"https://github.com/xbgmsharp/piwigo-openstreetmap\" target=\"_blank\">xbgmsharp</a>');\n"; }

$template->set_filename('map', dirname(__FILE__).'/template/osm-map.tpl' );

$template->assign(
	array(
		'CONTENT_ENCODING' => get_pwg_charset(),
		'OSM_PATH' => OSM_PATH,
		'PLUGIN_ROOT_URL' => get_absolute_root_url().'plugins/'.$osm_dir,
		'PLUGIN_LOCATION' => 'plugins/'.$osm_dir,
		'GALLERY_TITLE' => $conf['gallery_title'],
		'U_HOME' => make_index_url(),
		'U_HOME_MAP' => osm_make_map_index_url(),
		'OSMJS'	=> $js,
	)
);

$template->pparse('map');
$template->p();
?>