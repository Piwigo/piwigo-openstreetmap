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

$local_conf = array()
$local_conf['zoom'] = '2';
$local_conf['center_lat'] = '0';
$local_conf['center_lng'] = '0';
$local_conf['pinid'] = 1;
$local_conf['contextmenu'] = false;
$local_conf['available_pin'] = '';
$local_conf['control'] = false;
$local_conf['img_popup'] = false;

$js_data = osm_get_items($page);
$js = osm_get_js($conf, $local_conf, $js_data);
osm_gen_template($conf, 'osm-map.tpl', $js_data, $template)
?>
