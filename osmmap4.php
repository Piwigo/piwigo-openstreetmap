<?php
/***********************************************
* File      :   osmmap4.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display a world map v4
*
* Created   :   10.07.2015
*
* Copyright 2013-2016 <xbgmsharp@gmail.com>
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

$osm_is_active = osm_check_activated();
if ('active' !== $osm_is_active)
{
  echo 'OpeenStreetMap need to be activated!';
	return;
}

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

/* If the config include parameters get them */
$zoom = isset($conf['osm_conf']['left_menu']['zoom']) ? $conf['osm_conf']['left_menu']['zoom'] : 2;
$center = isset($conf['osm_conf']['left_menu']['center']) ? $conf['osm_conf']['left_menu']['center'] : '0,0';
$center_arr = preg_split('/,/', $center);
$center_lat = isset($center_arr) ? $center_arr[0] : 0;
$center_lng = isset($center_arr) ? $center_arr[1] : 0;

/* If we have zoom and center coordinate, set it otherwise fallback default */
if (isset($_GET['zoom'])) {
    check_input_parameter('zoom', $_GET, false, '/^1?\d$/',true);
    $zoom = $_GET['zoom'];
}
if (isset($_GET['center_lat'])) {
    check_input_parameter('center_lat', $_GET, false, '/^-?\d+(\.\d+)?$/',true);
    $center_lat = $_GET['center_lat'];
}
if (isset($_GET['center_lng'])) {
    check_input_parameter('center_lng', $_GET, false, '/^-?\d+(\.\d+)?$/',true);
    $center_lng = isset($_GET['center_lng']) ? $_GET['center_lng'] : $center_lng;
}

$local_conf = array();
$local_conf['zoom'] = $zoom;
$local_conf['center_lat'] = $center_lat;
$local_conf['center_lng'] = $center_lng;
$local_conf['contextmenu'] = 'true';
$local_conf['control'] = true;
$local_conf['img_popup'] = false;
$local_conf['paths'] = osm_get_gps($page);
$local_conf = $local_conf + $conf['osm_conf']['map'] + $conf['osm_conf']['left_menu'];

$js_data = osm_get_items($page);
$js = osm_get_js($conf, $local_conf, $js_data);
osm_gen_template($conf, $js, $js_data, 'osm-map4.tpl', $template);
?>
