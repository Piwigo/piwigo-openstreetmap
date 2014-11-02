<?php
/*
Plugin Name: OpenStreetMap
Version: 2.7.b
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

// Add GPX support file extensions
array_push($conf['file_ext'], 'gpx');

// Hook on to an event to display videos as standard images
add_event_handler('render_element_content', 'osm_render_media', EVENT_HANDLER_PRIORITY_NEUTRAL, 2);
function osm_render_media($content, $picture)
{
	global $template, $picture, $conf;

	//print_r( $picture['current']);
	// do nothing if the current picture is actually an image !
	if ( (array_key_exists('src_image', @$picture['current'])
		&& @$picture['current']['src_image']->is_original()) )
	{
		return $content;
	}
	// If not a GPX file
	if ( (array_key_exists('path', @$picture['current']))
		&& strpos($picture['current']['path'],".gpx") === false)
	{
		return $content;
	}

	$filename = embellish_url(get_gallery_home_url() . $picture['current']['element_url']);

	$local_conf = array();
	$local_conf['contextmenu'] = 'false';
	$local_conf['control'] = false;
	$local_conf['img_popup'] = false;
	$local_conf['popup'] = 2;
	$local_conf['center_lat'] = 0;
	$local_conf['center_lng'] = 0;
	$local_conf['zoom'] = '12';
	$local_conf['divname'] = 'mapgpx';

	$js_data = array(array(null, null, null, null, null, null, null, null));

	$js = osm_get_js($conf, $local_conf, $js_data);

	// Select the template
	$template->set_filenames(
            array('osm_content' => dirname(__FILE__)."/template/osm-gpx.tpl")
	);

	// Assign the template variables
	$template->assign(
        array(
            'HEIGHT'		=> '500',
            'FILENAME'		=> $filename,
            'OSM_PATH'		=> embellish_url(get_absolute_root_url().OSM_PATH),
            'OSMGPX'		=> $js,
            )
	);

	// Return the rendered html
	$osm_content = $template->parse('osm_content', true);
	return $osm_content;
}

// Hook to display a fallback thumbnail if not defined
add_event_handler('get_mimetype_location', 'osm_get_mimetype_icon');
function osm_get_mimetype_icon($location, $element_info)
{
	if ($element_info == 'gpx')
	{
		$location = 'plugins/'
			. basename(dirname(__FILE__))
			. '/mimetypes/'. $element_info. '.png';
	}
	return $location;
}

// Plugin on picture page
if (script_basename() == 'picture')
{
	include_once(dirname(__FILE__).'/picture.inc.php');
}
elseif (script_basename() == 'index')
{
    include_once(dirname(__FILE__).'/category.inc.php');
    include_once(dirname(__FILE__).'/menu.inc.php');
}

// Do we have to show a link on the left menu
if ($conf['osm_conf']['left_menu']['enabled'])
{
	// Hook to add link on the left menu
	add_event_handler('blockmanager_apply', 'osm_blockmanager_apply');
}

// Hook to add worldmap link on the album/category thumbnails
add_event_handler('loc_begin_index_category_thumbnails', 'osm_index_cat_thumbs_displayed');

// Hook to add worldmap link on the index thumbnails page
add_event_handler('loc_end_index', 'osm_end_index' );

function osm_index_cat_thumbs_displayed()
{
	global $page;
	$page['osm_cat_thumbs_displayed'] = true;
}

define('OSM_ACTION_MODEL', '<a href="%s" title="%s" rel="nofollow" class="pwg-state-default pwg-button"%s><span class="pwg-icon pwg-icon-globe">&nbsp;</span><span class="pwg-button-text">%s</span></a>');
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
	$link_title = sprintf( l10n('DISPLAY_ON_MAP'), strip_tags($page['title']) );
	$template->concat( 'PLUGIN_INDEX_ACTIONS' , "\n<li>".sprintf(OSM_ACTION_MODEL,
		$map_url, $link_title, '', 'map', l10n('MAP')
		).'</li>');
}

// If admin do the init
if (defined('IN_ADMIN')) {
	include_once(OSM_PATH.'/admin/admin_boot.php');
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
		$linkname = isset($conf['osm_conf']['left_menu']['link']) ? $conf['osm_conf']['left_menu']['link'] : l10n('OSWorldMap');
		$link_title = sprintf( l10n('DISPLAY_ON_MAP'), strip_tags($conf['gallery_title']) );
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

?>
