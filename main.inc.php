<?php
/*
Plugin Name: OpenStreetMap
Version: 0.3
Description: OpenStreetMap integration for piwigo
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=701
Author: xbmgsharp
Author URI: https://github.com/xbgmsharp/piwigo-openstreetmap
*/

// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Define the path to our plugin.
define('OSM_PATH', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)).'/');

global $conf;

// Prepare configuration
$conf['osm_conf'] = unserialize($conf['osm_conf']);

// Plugin on picture page
if (script_basename() == 'picture')
{
	include_once(dirname(__FILE__).'/picture.inc.php');
}

// Do we have to show a link on the left menu
if ($conf['osm_conf']['left_menu']['enabled'])
{
	// Hook to add link on the left menu
	add_event_handler('blockmanager_apply', 'osm_blockmanager_apply');
}

// Hook to sync geotag metadata on updload
if ($conf['osm_conf']['auto_sync'])
{
	$conf['use_exif_mapping']['lat'] = 'lat';
	$conf['use_exif_mapping']['lon'] = 'lon';
	add_event_handler('format_exif_data', 'osm_format_exif_data', EVENT_HANDLER_PRIORITY_NEUTRAL, 3);
}


// Hook to a admin config page
add_event_handler('get_admin_plugin_menu_links', 'osm_admin_menu');

function osm_admin_menu($menu)
{
	array_push($menu,
		array(
			'NAME' => 'OpenStreetMap',
			'URL'  => get_admin_plugin_menu_link(dirname(__FILE__).'/admin.php')
		)
	);
	return $menu;
}

function osm_format_exif_data($exif, $file, $map)
{
	if (isset($map['lat']))
	{
		include_once( dirname(__FILE__) .'/include/functions_metadata.php');
		$ll = osm_exif_to_lat_lon($exif);
		if (is_array($ll))
		{
			$exif[$map['lat']] = $ll[0];
			$exif[$map['lon']] = $ll[1];
		}
	}
	return $exif;
}

function osm_blockmanager_apply($mb_arr)
{
	if ($mb_arr[0]->get_id() != 'menubar' )
		return;
	if ( ($block=$mb_arr[0]->get_block('mbMenu')) != null )
	{
		include_once( dirname(__FILE__) .'/include/functions.php');
		load_language('plugin.lang', OSM_PATH);
		global $conf;
		$linkname = isset($conf['osm_conf']['left_menu']['link']) ? $conf['osm_conf']['left_menu']['link'] : 'OS World Map';
		$link_title = sprintf( l10n('displays %s on a map'), strip_tags($conf['gallery_title']) );
		$block->data['osm'] = array(
			'URL' => osm_make_map_index_url( array('section'=>'categories') ),
			'TITLE' => $link_title,
			'NAME' => $linkname,
			'REL'=> 'rel=nofollow'
		);
	}
}

function osm_strbool($value)
{
	return $value ? 'true' : 'false';
}

function imagery($bl, $style){
	$return = "";
	if     ($bl == 'mapnik')	$return = "OSM.org (CC BY-SA)";
	else if($bl == 'mapnikfr')	$return = "Openstreetmap.fr (CC BY-SA)";
	else if($bl == 'mapnikde')	$return = "Openstreetmap.de (CC BY-SA)";
	else if($bl == 'cloudmade')	$return = "Cloudmade (CC BY-SA)";
	else if($bl == 'mapquest')	$return = "Mapquest (CC BY-SA)";
	else if($bl == 'custom')	$return = $style;
	return $return;
}

?>
