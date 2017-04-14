<?php
/***********************************************
* File      :   admin_place.php
* Project   :   piwigo-openstreetmap
* Descr     :   Create place for reuse
*
* Created   :   07.07.2015
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

// Check whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Check access and exit when user status is not ok
check_status(ACCESS_ADMINISTRATOR);

// Setup plugin Language
load_language('plugin.lang', OSM_PATH);

// Fetch the template.
global $template, $conf, $lang, $prefixeTable;
// Easy access
define('osm_place_table', $prefixeTable.'osm_places');

/* Table to hold osm places details */
$q = 'CREATE TABLE IF NOT EXISTS `'.osm_place_table.'` (
                `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                `latitude` double(8,6) NOT NULL,
                `longitude` double(9,6) NOT NULL,
                `name` varchar(255) DEFAULT NULL,
                `parentId` mediumint(8),
                PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
        ;';
pwg_query($q);


// +-----------------------------------------------------------------------+
// |                                edit places                            |
// +-----------------------------------------------------------------------+

if (isset($_POST['edit_submit']) and isset($_POST['edit_list']))
{
  $query = '
SELECT name
  FROM '.osm_place_table.'
  WHERE id NOT IN ('.$_POST['edit_list'].')
;';
  $existing_names = array_from_query($query, 'name');

  $current_name_of = array();
  $query = '
SELECT id, name, latitude, longitude
  FROM '.osm_place_table.'
  WHERE id IN ('.$_POST['edit_list'].')
;';
  $result = pwg_query($query);
  while ($row = pwg_db_fetch_assoc($result))
  {
    $current_name_of[ $row['id'] ] = $row['name'];
  }

  $updates = array();
  // we must not rename tag with an already existing name
  foreach (explode(',', $_POST['edit_list']) as $place_id)
  {
    $place_name = stripslashes($_POST['place_name-'.$place_id]);
    $place_lat = stripslashes($_POST['place_lat-'.$place_id]);
    $place_lon = stripslashes($_POST['place_lon-'.$place_id]);

    if (in_array($place_name, $existing_names))
    {
        $page['errors'][] = l10n('Place "%s" already exists', $place_name);
    }
    else if (!empty($place_name))
    {
        $updates[] = array(
          'id' => $place_id,
          'name' => addslashes($place_name),
          'latitude' => $place_lat,
          'longitude' => $place_lon,
          );
     }
  }
  mass_updates(
    osm_place_table,
    array(
      'primary' => array('id'),
      'update' => array('name', 'latitude', 'longitude'),
      ),
    $updates
    );
}

// +-----------------------------------------------------------------------+
// |                               delete places                           |
// +-----------------------------------------------------------------------+

if (isset($_POST['delete']) and isset($_POST['places']))
{
  $query = '
SELECT name
  FROM '.osm_place_table.'
  WHERE id IN ('.implode(',', $_POST['places']).')
;';
  $place_names = array_from_query($query, 'name');

  $query = '
DELETE
  FROM '.osm_place_table.'
  WHERE id IN ('.implode(',', $_POST['places']).')
;';
  pwg_query($query);

  $page['infos'][] = l10n_dec(
    'The following place was deleted', 'The %d following places were deleted',
    count($place_names)
    )
  .' : '.implode(', ', $place_names);
}

// +-----------------------------------------------------------------------+
// |                               add a place                             |
// +-----------------------------------------------------------------------+
if (isset($_POST['add']) and !empty($_POST['add_place']))
{
  $query = "INSERT INTO `".osm_place_table."` (`name`, `latitude`, `longitude`) VALUE ('". $_POST['add_place'] ."', '". $_POST['add_lat'] ."', '". $_POST['add_lon'] ."');";
  $result = pwg_query($query);
}

// all places
$query = 'SELECT * FROM `'.osm_place_table.'`;';
$result = pwg_query($query);
$all_places = array();
while ($place = pwg_db_fetch_assoc($result))
{
  $all_places[] = $place;
}

// Send value to templates
$template->assign(
	array(
		'OSM_PATH' => OSM_PATH,
		'all_places' => $all_places,
	)
);

if (isset($_POST['edit']) and isset($_POST['places']))
{
  $list_name = 'EDIT_PLACES_LIST';
  if (isset($_POST['duplicate']))
  {
    $list_name = 'DUPLIC_TAGS_LIST';
  }
  elseif (isset($_POST['merge']))
  {
    $list_name = 'MERGE_TAGS_LIST';
  }

  $template->assign($list_name, implode(',', $_POST['places']));

  $query = '
SELECT id, name, latitude, longitude
  FROM '.osm_place_table.'
  WHERE id IN ('.implode(',', $_POST['places']).')
;';
  $result = pwg_query($query);
  while ($row = pwg_db_fetch_assoc($result))
  {
    $template->append(
      'places',
      array(
        'ID' => $row['id'],
        'NAME' => $row['name'],
        'LATITUDE' => $row['latitude'],
        'LONGITUDE' => $row['longitude'],
        )
      );
  }
}


?>
