<?php
/***********************************************
* File      :   admin_sync.php
* Project   :   piwigo-openstreetmap
* Descr     :   Generate the admin panel
* Base on   :   RV Maps & Earth plugin
*
* Created   :   12.06.2013
*
* Copyright 2013 <xbgmsharp@gmail.com>
* Contribution Marjolein 2013
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

// Generate default value
$sync_options = array(
	'overwrite'		=> true,
	'simulate'		=> true,
	'cat_id'		=> 0,
	'subcats_included'	=> true,
);

if ( isset($_POST['submit']) )
{
	// Override default value from the form
	$sync_options = array(
		'overwrite' => isset($_POST['overwrite']),
		'simulate' => isset($_POST['simulate']),
		'cat_id' => isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0,
		'subcats_included' => isset($_POST['subcats_included']),
	);

	include_once( dirname(__FILE__).'/../include/functions_metadata.php' );
	// Define files which support EXIF headers from JPEG or JPG or TIFF or TIF
	define('SQL_EXIF', "(LOWER(`path`) LIKE '%.jpeg' OR LOWER(`path`) LIKE '%.jpg' OR LOWER(`path`) LIKE '%.tiff' OR LOWER(`path`) LIKE '%.tif')");
	if ( $sync_options['cat_id']!=0 )
	{
		$query=' SELECT id FROM '.CATEGORIES_TABLE.' WHERE ';

		if ( $sync_options['subcats_included'])
			$query .= 'uppercats REGEXP \'(^|,)'.$sync_options['cat_id'].'(,|$)\'';
		else
			$query .= 'id='.$sync_options['cat_id'];
			$cat_ids = array_from_query($query, 'id');

		$query='SELECT `id`, `path` ,`lat` ,`lon` FROM '.IMAGES_TABLE.' INNER JOIN '.IMAGE_CATEGORY_TABLE.' ON id=image_id
			WHERE '. SQL_EXIF .' AND category_id IN ('.implode(',', $cat_ids).')
			GROUP BY id';
	}
	else
	{
		$query='SELECT `id`, `path` ,`lat` ,`lon` FROM '.IMAGES_TABLE.' WHERE '. SQL_EXIF;
	}

	$images = hash_from_query( $query, 'id');
	$datas = array();
	$errors = array();
	$warnings = array();
	$infos = array();
	foreach ($images as $image)
	{
		$filename = $image['path'];
		// Properly detect if EXIF information does not exist: http://php.net/exif_read_data
		$exif = exif_read_data($filename, 'ANY_TAG');
		if ( $exif === false)
		{
			$warnings[] = $filename.': no EXIF data found';
			continue;
		}

		// At this point there _is_ EXIF data in the file but it may not contain geo tags
		$ll = osm_exif_to_lat_lon($exif);
		if (!is_array($ll))
		{
			$infos[] = $filename.': no lat-lon data in EXIF';
			if (!empty($ll))
				$errors[] = $filename. ': '.$ll;
			continue;
		}

		// At this point there is valid geo data in the EXIF
		$infos[] = $filename.': lat-lon data found in EXIF';
		if ($sync_options['overwrite'])
		{
			if (!empty($image['lat']) || !empty($image['lon']))
			{
				$warnings[] = $filename.': skipped because DB already has geo data: lat '.$image['lat'].' lon '.$image['lon'];
				continue;
			}
		}

		# At this point the database either has no lat-lon data or it may be overwritten
		$datas[] = array (
			'id'	=> $image['id'],
			'lat'	=> $ll[0],
			'lon'	=> $ll[1],
		);
	}

	if ( count($datas)>0 and !$sync_options['simulate'] )
	{
		mass_updates(
			IMAGES_TABLE,
				array(
					'primary'	=> array('id'),
					'update'	=> array('lat', 'lon')
				),
				$datas
		);
	}


	// Send sync result to template
	$template->assign('sync_errors', $errors );
	$template->assign('sync_warnings', $warnings );
	$template->assign('sync_infos', $infos );

	// Send result to templates
	$template->assign(
		'metadata_result',
		array(
			'NB_ELEMENTS_DONE'			=> count($datas),
			'NB_ELEMENTS_CANDIDATES'	=> count($images),
			'NB_ERRORS'					=> count($errors),
			'NB_WARNINGS'				=> count($warnings),
		)
	);
}

$query = 'SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE `lat` IS NOT NULL and `lon` IS NOT NULL ';
list($nb_geotagged) = pwg_db_fetch_array( pwg_query($query) );

$query = 'SELECT id, CONCAT(name, IF(dir IS NULL, " (V)", "") ) AS name, uppercats, global_rank  FROM '.CATEGORIES_TABLE;
display_select_cat_wrapper($query,
                           array( $sync_options['cat_id'] ),
                           'categories',
                           false);

// Send value to templates
$template->assign(
	array(
		'SUBCATS_INCLUDED_CHECKED' 	=> $sync_options['subcats_included'] ? 'checked="checked"' : '',
		'NB_GEOTAGGED' 			=> $nb_geotagged,
	)
);

?>
