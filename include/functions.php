<?php
/***********************************************
* File      :   functions.php
* Project   :   piwigo-openstreetmap
* Descr     :   Read Geotag Metdata
* Base on   :   RV Maps & Earth plugin
*
* Created   :   30.05.2013
*
* Copyright 2013-2015 <xbgmsharp@gmail.com>
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

function osm_get_cache_file_name()
{
	global $conf;
	return PHPWG_ROOT_PATH.$conf['data_location'].'/tmp/_rvgm_cat_cache.dat';
}

function osm_invalidate_cache()
{
	@unlink(  osm_get_cache_file_name() );
}

function osm_load_language()
{
	global $lang,$lang_info,$conf;
	if ( isset($lang['Map']) or ($lang_info['code']=='en' and !$conf['debug_l10n']) )
		return;
	load_language('lang', dirname(__FILE__).'/../');
}

function osm_items_have_latlon($items)
{
  $query = '
SELECT id FROM '.IMAGES_TABLE.'
WHERE latitude IS NOT NULL
  AND id IN ('.implode(',', $items).')
ORDER BY NULL
LIMIT 0,1';
	if ( pwg_db_num_rows(pwg_query($query))> 0)
		return true;
	return false;
}

function osm_make_map_picture_url($params)
{
	$map_url = make_picture_url($params);
	return add_url_params($map_url, array('map'=>null) );
}

function osm_duplicate_map_picture_url()
{
	$map_url = duplicate_picture_url();
	return add_url_params($map_url, array('map'=>null) );
}

function osm_make_map_index_url($params=array())
{
	global $conf, $osm_dir;
	$url = get_root_url().'osmmap';
	if ($conf['php_extension_in_urls'])
		$url .= '.php';
	if ($conf['question_mark_in_urls'])
		$url .= '?';
	$url .= make_section_in_url($params);
	$url = add_well_known_params_in_url($url, array_intersect_key($params, array('flat'=>1) ) );
	return $url;
}

function osm_duplicate_map_index_url($redefined=array(), $removed=array())
{
	return osm_make_map_index_url(
		params_for_duplication($redefined, $removed)
	);
}

function osm_duplicate_kml_index_url($redefined=array(), $removed=array())
{
	return osm_make_kml_index_url(
		params_for_duplication($redefined, $removed)
	);
}

function osm_make_kml_index_url($params)
{
	global $conf, $osm_dir;
	$url = get_root_url().'plugins/'.$osm_dir.'/kml.php';
	if ($conf['question_mark_in_urls'])
		$url .= '?';

	$url .= make_section_in_url($params);
	unset( $params['start'] );
	if ( 'categories'!=$params['section']) unset( $params['flat'] );
	$url = add_well_known_params_in_url($url, $params);
	$get_params = array();
	if ( isset($params['box']) and !empty($params['box']) )
	{
		include_once( dirname(__FILE__).'/functions_map.php' );
		if ( ! bounds_is_world($params['box']) )
			$get_params['box'] = bounds_to_url($params['box']);
	}
	if ( isset($params['ll']) and !empty($params['ll']) )
		$get_params['ll'] = $params['ll']['lat'].','.$params['ll']['lon'];
	$url = add_url_params($url, $get_params );
	return $url;
}
?>
