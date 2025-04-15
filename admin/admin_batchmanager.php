<?php
/***********************************************
* File      :   admin_batchmanager.php
* Project   :   piwigo-openstreetmap
* Descr     :   handle batch manager
* Base on   :   RV Maps & Earth plugin
*
* Created   :   4.06.2013
*
* Copyright 2012-2015 <xbgmsharp@gmail.com>
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

include_once( OSM_PATH .'/include/functions.php');

// Hook to add a new filter in the batch mode
add_event_handler('get_batch_manager_prefilters', 'osm_get_batch_manager_prefilters');
function osm_get_batch_manager_prefilters($prefilters)
{
	$prefilters[] = array('ID' => 'osm0', 'NAME' => l10n('OSM Geotagged'));
	$prefilters[] = array('ID' => 'osm1', 'NAME' => l10n('OSM Not geotagged'));
	$prefilters[] = array('ID' => 'osm2', 'NAME' => l10n('OSM GPX tracks'));
	return $prefilters;
}

// Hook to perfom the filter in the batch mode
add_event_handler('perform_batch_manager_prefilters', 'osm_perform_batch_manager_prefilters', 50, 2);
function osm_perform_batch_manager_prefilters($filter_sets, $prefilter)
{
	if ($prefilter==="osm0")
		$filter = "`latitude` IS NOT NULL and `longitude` IS NOT NULL";
	else if ($prefilter==="osm1")
		$filter = "`latitude` IS NULL OR `longitude` IS NULL";
	else if ($prefilter==="osm2")
		$filter = "`path` LIKE '%.gpx%'";

	if ( isset($filter) )
	{
		$query = "SELECT id FROM ".IMAGES_TABLE." WHERE ".$filter;
		$filter_sets[] = array_from_query($query, 'id');
	}
	return $filter_sets;
}

// Hook to show action when selected
add_event_handler('loc_end_element_set_global', 'osm_loc_end_element_set_global');

function osm_loc_end_element_set_global()
{
  global $template, $conf;

	// Save location, eg Place
	$available_places = array();
	$place_options = array();
	$query = '
	SELECT id, name, latitude, longitude
	  FROM '.osm_place_table.'
          ORDER BY name
	;';
	$result = pwg_query($query);
	// JS for the template
	while ($row = pwg_db_fetch_assoc($result))
	{
		$available_places[$row['id']] =  $row['name'];
		$place_options[] = '<option value="' . $row['id'] . '">' . $row['name'] . '</options>';
	}
	$jsplaces = "\nvar arr_places = ". json_encode(get_list_of_places()) .";\n";

	$batch_global_height = isset($conf['osm_conf']['batch']['global_height']) ? $conf['osm_conf']['batch']['global_height'] : '200';
	
  $template -> assign(
    array(
      'jsplaces' => $jsplaces,
      'place_options' => $place_options,
      'batch_global_height' => $batch_global_height,
    )
  );

  $template->set_filename('OSM_batch_global', OSM_PATH.'admin/batch_global.tpl');
  
  $template->append('element_set_global_plugins_actions',
		array(
      'ID' => 'openstreetmap', 
      'NAME'=>l10n('OSM GeoTag'), 
      'CONTENT' => $template->parse('OSM_batch_global', true)
    )
  );

  if($conf['osm_conf']['community_bm']['enabled'])
  {
    //Used in community
    $template->append('community_element_set_global_plugins_actions',
      array(
        'ID' => 'openstreetmap', 
        'NAME'=>l10n('OSM GeoTag'), 
        'CONTENT' => $template->parse('OSM_batch_global', true)
      )
    );
  }


}

// Hook to perform the action on in global mode
add_event_handler('element_set_global_action', 'osm_element_set_global_action', 50, 2);
function osm_element_set_global_action($action, $collection)
{
	if ($action!=="openstreetmap")
		return;

	global $page;

	$lat = trim($_POST['osmlat']);
	$lon = trim($_POST['osmlon']);
	if ( strlen($lat)>0 and strlen($lon)>0 )
	{
		if ( is_numeric($lat) and is_numeric($lon)
			and (double)$lat<=90 and (double)$lat>=-90
			and (double)$lon<=180 and (double)$lon>=-180 )
			$update_query = 'latitude='.$lat.', longitude='.$lon;
		else
			$page['errors'][] = 'Invalid latitude or longitude value';
	}
	elseif ( strlen($lat)==0 and strlen($lon)==0 )
		$update_query = 'latitude=NULL, longitude=NULL';
	else
		$page['errors'][] = 'Both latitude/longitude must be empty or not empty';

	if (isset($update_query))
	{
		$update_query = 'UPDATE '.IMAGES_TABLE.' SET '.$update_query.'
					WHERE id IN ('.implode(',',$collection).')';
		pwg_query($update_query);
	}
}



// Hoook to add tpl in batch manager in single mode
add_event_handler('loc_end_element_set_unit', 'osm_loc_end_element_set_unit');
function osm_loc_end_element_set_unit()
{
  global $template, $page;

	$batch_unit_height = isset($conf['osm_conf']['batch']['unit_height']) ? $conf['osm_conf']['batch']['unit_height'] : '200';
	
  $template->assign(array(
      'OSM_PATH' => OSM_PATH,
      'batch_unit_height' => $batch_unit_height,
  ));

  $template->set_filename('OSM_batch_unit', dirname(__FILE__).'/batch_unit.tpl');
  
  $template->append('PLUGINS_BATCH_MANAGER_UNIT_ELEMENT_SUBTEMPLATE',dirname(__FILE__).'/batch_unit.tpl');
}
