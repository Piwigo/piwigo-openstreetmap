<?php
/***********************************************
* File      :   osmmap.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display a world map
*
* Created   :   28.05.2013
*
* Copyright 2013-2014 <xbgmsharp@gmail.com>
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
include_once( PHPWG_ROOT_PATH.'admin/include/functions.php' );
include_once( dirname(__FILE__) .'/include/functions.php');
include_once( dirname(__FILE__) .'/include/functions_map.php');

check_status(ACCESS_GUEST);

osm_load_language();
load_language('plugin.lang', OSM_PATH);

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

$js_data = osm_get_items($page);

// Load parameter, fallback to default if unset
$linkname = isset($conf['osm_conf']['left_menu']['link']) ? $conf['osm_conf']['left_menu']['link'] : 'OS World Map';
$popup = isset($conf['osm_conf']['left_menu']['popup']) ? $conf['osm_conf']['left_menu']['popup'] : 0;
$popupinfo_name = isset($conf['osm_conf']['left_menu']['popupinfo_name']) ? $conf['osm_conf']['left_menu']['popupinfo_name'] : 0;
$popupinfo_img = isset($conf['osm_conf']['left_menu']['popupinfo_img']) ? $conf['osm_conf']['left_menu']['popupinfo_img'] : 0;
$popupinfo_link = isset($conf['osm_conf']['left_menu']['popupinfo_link']) ? $conf['osm_conf']['left_menu']['popupinfo_link'] : 0;
$popupinfo_comment = isset($conf['osm_conf']['left_menu']['popupinfo_comment']) ? $conf['osm_conf']['left_menu']['popupinfo_comment'] : 0;
$popupinfo_author  = isset($conf['osm_conf']['left_menu']['popupinfo_author']) ? $conf['osm_conf']['left_menu']['popupinfo_author'] : 0;
$baselayer = isset($conf['osm_conf']['map']['baselayer']) ? $conf['osm_conf']['map']['baselayer'] : 'mapnik';
$custombaselayer = isset($conf['osm_conf']['map']['custombaselayer']) ? $conf['osm_conf']['map']['custombaselayer'] : '';
$custombaselayerurl = isset($conf['osm_conf']['map']['custombaselayerurl']) ? $conf['osm_conf']['map']['custombaselayerurl'] : '';
$noworldwarp = isset($conf['osm_conf']['map']['noworldwarp']) ? $conf['osm_conf']['map']['noworldwarp'] : 'false';
$attrleaflet = isset($conf['osm_conf']['map']['attrleaflet']) ? $conf['osm_conf']['map']['attrleaflet'] : 'false';
$attrimagery = isset($conf['osm_conf']['map']['attrimagery']) ? $conf['osm_conf']['map']['attrimagery'] : 'false';
$attrmodule = isset($conf['osm_conf']['map']['attrplugin']) ? $conf['osm_conf']['map']['attrplugin'] : 'false';
$zoom = '2';
$center_lat = '0';
$center_lng = '0';
// Load baselayerURL
// Key1 BC9A493B41014CAABB98F0471D759707
if     ($baselayer == 'mapnik')		$baselayerurl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
else if($baselayer == 'mapquest')	$baselayerurl = 'http://otile1.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
else if($baselayer == 'cloudmade')	$baselayerurl = 'http://{s}.tile.cloudmade.com/7807cc60c1354628aab5156cfc1d4b3b/997/256/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikde')	$baselayerurl = 'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikfr')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png';
else if($baselayer == 'blackandwhite')	$baselayerurl = 'http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png';
else if($baselayer == 'mapnikhot')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
else if($baselayer == 'mapquestaerial')	$baselayerurl = 'http://oatile{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.jpg';
else if($baselayer == 'custom')	$baselayerurl = $custombaselayerurl;

$attribution = osmcopyright($attrleaflet, $attrimagery, $attrmodule, $baselayer, $custombaselayer);

// Generate Javascript
// ----------------------------------------
// no worldWarp (no world copies, restrict the view to one world)
if($noworldwarp)
{
	$nowarp = " true ";
	$worldcopyjump = "worldCopyJump: false, maxBounds: [ [82, -180], [-82, 180] ]";
}
else
{
	$nowarp = " false ";
	$worldcopyjump = "worldCopyJump: true";
}

//$js = "\nvar addressPoints = ". json_encode($js_data, JSON_UNESCAPED_SLASHES) .";\n";
$js = "\nvar addressPoints = ". str_replace("\/","/",json_encode($js_data)) .";\n";


// Create the map and get a new map instance attached and element with id="tile-map"
$js .= "\nvar Url = '".$baselayerurl."',
	Attribution = '".$attribution."',
	TileLayer = new L.TileLayer(Url, {maxZoom: 18, noWrap: ".$nowarp.", attribution: Attribution}),
	latlng = new L.LatLng(".$center_lat.", ".$center_lng.");\n";
$js .= "var map = new L.Map('map', {center: latlng, zoom: ".$zoom.", layers: [TileLayer]});\n";
$js .= "map.attributionControl.setPrefix('');\n";
$js .= "var markers = new L.MarkerClusterGroup();\n";
$js .= "for (var i = 0; i < addressPoints.length; i++) {
	var a = addressPoints[i];
	var title = a[2];
	var pathurl = a[3];
	var imgurl = a[4];
	var comment = a[5];
	var author = a[6];
	var width = a[7];
	var latlng = new L.LatLng(a[0], a[1]);
	var marker = new L.Marker(latlng, { title: title });
	";

// create Popup
if ($popup < 2)
{
	$openpopup = ".openPopup()";
	$myinfo = "'<p>";
	if($popupinfo_name)
	{
		$myinfo .= "'+title+'";
	}
	if($popupinfo_img and !$popupinfo_link)
	{
		$myinfo .= "<br /><img src=\"'+pathurl+'\">";
	}
	else if($popupinfo_img and $popupinfo_link)
	{
		$myinfo .= "<br /><a href=\"'+imgurl+'\"><img src=\"'+pathurl+'\"></a>";
	}
	if($popupinfo_comment)
	{
		$myinfo .= "<br />'+comment+'";
	}
	if($popupinfo_author)
	{
		$myinfo .= "<br />'+author+'";
	}
	$myinfo .= "</p>'";
	$js .= "marker.bindPopup(".$myinfo.", {minWidth: '+width+'}).openPopup();";
}

	$js .= "\tmarkers.addLayer(marker);\n
}";
$js .= "\nmap.addLayer(markers);\n";

$template->set_filename('map', dirname(__FILE__).'/template/osm-map.tpl' );

$template->assign(
	array(
		'CONTENT_ENCODING'	=> get_pwg_charset(),
		'OSM_PATH'			=> embellish_url(get_absolute_root_url().OSM_PATH),
		'GALLERY_TITLE'		=> $linkname .' - '. $conf['gallery_title'],
		'HOME'              => make_index_url(),
		'HOME_PREV'         => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : get_absolute_root_url(),
		'HOME_NAME'         => l10n("Home"),
		'HOME_PREV_NAME'    => l10n("Previous"),
		'TOTAL'             => sprintf( l10n('%d items'), count($php_data) ),
		'OSMJS'				=> $js,
	)
);

$template->pparse('map');
$template->p();
?>
