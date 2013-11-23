<?php
/*
Plugin Name: OpenStreetMap
Version: 0.8
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

// Hook to sync geotag metadata on upload
if ($conf['osm_conf']['auto_sync'])
{
	$conf['use_exif_mapping']['lat'] = 'lat';
	$conf['use_exif_mapping']['lon'] = 'lon';
	add_event_handler('format_exif_data', 'osm_format_exif_data', EVENT_HANDLER_PRIORITY_NEUTRAL, 3);
}

// Hook to add link on the album/category thumbnails
add_event_handler('loc_begin_index_category_thumbnails', 'osm_index_cat_thumbs_displayed');

// Hook to add link on the index thumbnails page
add_event_handler('loc_end_index', 'osm_end_index' );

function osm_index_cat_thumbs_displayed()
{
	global $page;
	$page['osm_cat_thumbs_displayed'] = true;
}

define('OSM_ACTION_MODEL', '<a href="%s" title="%s" rel="nofollow" class="pwg-state-default pwg-button"%s><span class="pwg-icon pwg-icon-%s">&nbsp;</span><span class="pwg-button-text">%s</span></a>');
function osm_end_index()
{
	global $page, $filter, $template;

	if ( isset($page['chronology_field']) || $filter['enabled'] )
		return;

	if ( 'categories' == @$page['section'])
	{ // flat or no flat ; has subcats or not;  ?
		if ( ! @$page['osm_cat_thumbs_displayed'] and empty($page['items']) )
			return;
	}
	else
	{
		if (
			!in_array( @$page['section'], array('tags','search','recent_pics','list') )
			)
			return;
		if ( empty($page['items']) )
			return;
	}

	include_once( dirname(__FILE__) .'/include/functions.php');

	if ( !empty($page['items']) )
	{
		if (!@$page['osm_items_have_latlon'] and ! osm_items_have_latlon( $page['items'] ) )
			return;
	}
	osm_load_language();

	$map_url = osm_duplicate_map_index_url( array(), array('start') );
	$link_title = sprintf( l10n('displays %s on a map'), strip_tags($page['title']) );
	$template->concat( 'PLUGIN_INDEX_ACTIONS' , "\n<li>".sprintf(OSM_ACTION_MODEL,
		$map_url, $link_title, '', 'map', l10n('Map')
		).'</li>');
}

// If admin do the init
if (defined('IN_ADMIN')) {
	include_once(OSM_PATH.'/admin/admin_boot.php');
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

function osmcopyright($attrleaflet, $attrimagery, $attrmodule, $bl, $custombaselayer)
{
	$return = "";

	if ($attrleaflet) $return .= '<a href="http://leafletjs.com/" target="_blank">Leaflet</a> ';

	if ($attrmodule) $return .= l10n('PLUGINBY').' <a href="https://github.com/xbgmsharp/piwigo-openstreetmap" target="_blank">xbgmsharp</a> ';

	if ($attrimagery)
	{
		$return .= " ";
		if     ($bl == 'mapnik')	$return .= "Tiles Courtesy of OSM.org (CC BY-SA)";
		else if($bl == 'mapnikfr')	$return .= "Tiles Courtesy of Openstreetmap.fr (CC BY-SA)";
		else if($bl == 'mapnikde')	$return .= "Tiles Courtesy of Openstreetmap.de (CC BY-SA)";
		else if($bl == 'blackandwhite')	$return .= "Tiles Courtesy of OSM.org (CC BY-SA)";
		else if($bl == 'mapnikhot')	$return .= 'Tiles Courtesy of &copy; <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>';
		else if($bl == 'cloudmade')	$return .= 'Tiles Courtesy of &copy; <a href="http://cloudmade.com">CloudMade</a> ';
		else if($bl == 'mapquest')	$return .= 'Tiles Courtesy of &copy; <a href="http://www.mapquest.com/">MapQuest</a>';
		else if($bl == 'mapquestaerial')	$return .= 'Tiles Courtesy of <a href="http://www.mapquest.com/">MapQuest</a> &mdash; Portions Courtesy NASA/JPL-Caltech and U.S. Depart. of Agriculture, Farm Service Agency';
		else if($bl == 'custom')	$return .= $custombaselayer;
	}
	// Mandatory by http://www.openstreetmap.org/copyright
	$return .= ' &copy; <a href="http://www.openstreetmap.org" target="_blank">OpenStreetMap</a> contributors, (<a href="http://www.openstreetmap.org/copyright" target="_blank">ODbL</a>)';
	return $return;
}

?>
