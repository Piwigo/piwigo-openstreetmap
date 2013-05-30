<?php
/***********************************************
* File      :   maintain.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Install / Uninstall method
*
* Created   :   28.05.2013
*
* Copyright 2013 <xbgmsharp@gmail.com>
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
global $template, $conf, $lang;

// Load parameter
$add_before = $conf['osm_add_before'];
$height = $conf['osm_height'];
$zoom = $conf['osm_zoom'];
$baselayer = $conf['osm_baselayer'];
$custombaselayer = $conf['osm_custombaselayer'];
$custombaselayerurl = $conf['osm_custombaselayerurl'];
$noworldwarp = $conf['osm_noworldwarp'] ? 'true' : 'false';
$attrleaflet = $conf['osm_attrleaflet'] ? 'true' : 'false';
$attrimagery = $conf['osm_attrimagery'] ? 'true' : 'false';
$attrmodule = $conf['osm_attrmodule'] ? 'true' : 'false';
$auto_sync = $conf['osm_auto_sync'] ? 'true' : 'false';

// Available baselayer
$available_baselayer = array(
	'mapnik' => 'OpenStreetMap (Mapnik)',
	'mapnikfr' => 'OpenStreetMap FR',
	'mapnikde' => 'OpenStreetMap DE',
	'mapquest' => 'MapQuest',
	'cloudmade' => 'Cloudmade',
	'custom' => 'custom',
);

// Available zoom value
$available_zoom = array(
	'1' => '1',
	'2' => '2',
	'3' => '3',
	'4' => '4',
	'5' => '5',
	'6' => '6',
	'7' => '7',
	'8' => '8',
	'9' => '9',
	'10' => '10',
	'11' => '11',
	'12' => '12',
	'13' => '13',
	'14' => '14',
	'15' => '15',
	'16' => '16',
	'17' => '17',
	'18' => '18',
);

$available_add_before = array(
	'Author' => l10n('Author'),
	'datecreate' => l10n('Created on'),
	'datepost' => l10n('Posted on'),
	'Dimensions' => l10n('Dimensions'),
	'File' => l10n('File'),
	'Filesize' => l10n('Filesize'),
	'Tags' => l10n('tags'),
	'Categories' => l10n('Albums'),
	'Visits' => l10n('Visits'),
	'Average' => l10n('Rating score'),
	'rating' => l10n('Rate this photo'),
	'Privacy' => l10n('Who can see this photo?'),
);

$query = 'SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE `lat` IS NOT NULL and `lon` IS NOT NULL ';
list($nb_geotagged) = pwg_db_fetch_array( pwg_query($query) );

// Update conf if submitted in admin site
if (isset($_POST['submit']) && !empty($_POST['osm_height']))
{
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_add_before'] ."' WHERE param='osm_add_before'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_height'] ."' WHERE param='osm_height'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_zoom'] ."' WHERE param='osm_zoom'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_baselayer'] ."' WHERE param='osm_baselayer'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_custombaselayer'] ."' WHERE param='osm_custombaselayer'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_custombaselayerurl'] ."' WHERE param='osm_custombaselayerurl'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_noworldwarp'] ."' WHERE param='osm_noworldwarp'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_attrleaflet'] ."' WHERE param='osm_attrleaflet'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_attrimagery'] ."' WHERE param='osm_attrimagery'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_attrmodule'] ."' WHERE param='osm_attrmodule'";
	pwg_query($query);
	$query = "UPDATE ". CONFIG_TABLE ." SET value='". $_POST['osm_auto_sync'] ."' WHERE param='osm_auto_sync'";
	pwg_query($query);

	// keep the value in the admin form
	$add_before = $_POST['osm_add_before'];
	$heigh = $_POST['osm_height'];
	$zoom = $_POST['osm_zoom'];
	$baselayer = $_POST['osm_baselayer'];
	$custombaselayer = $_POST['osm_custombaselayer'];
	$custombaselayerurl = $_POST['osm_custombaselayerurl'];
	$noworldwarp = $_POST['osm_noworldwarp'];
	$attrleaflet = $_POST['osm_attrleaflet'];
	$attrimagery = $_POST['osm_attrimagery'];
	$attrmodule = $_POST['osm_attrmodule'];
	$auto_sync = $_POST['osm_auto_sync'];
	
	$template->delete_compiled_templates();
	array_push($page['infos'], l10n('Your configuration settings are saved'));
}

// send value to template
$template->assign(
	array(
		'HEIGHT'		=> $height,
		'SELECTED_ADD_BEFORE'	=> $add_before,
		'AVAILABLE_ADD_BEFORE'	=> $available_add_before,
		'SELECTED_ZOOM'		=> $zoom,
		'AVAILABLE_ZOOM'	=> $available_zoom,
		'SELECTED_BASELAYER'	=> $baselayer,
		'AVAILABLE_BASELAYER'	=> $available_baselayer,
		'CUSTOMBASELAYER'	=> $custombaselayer,
		'CUSTOMBASELAYERURL'	=> $custombaselayerurl,
		'NOWORLDWARP'		=> $noworldwarp,
		'ATTRLEAFLET'		=> $attrleaflet,
		'ATTRIMAGERY'		=> $attrimagery,
		'ATTRMODULE'		=> $attrmodule,
		'AUTO_SYNC'		=> $auto_sync,
		'NB_GEOTAGGED' 		=> $nb_geotagged,
	)
);

// Add our template to the global template
$template->set_filenames(
	array(
		'plugin_admin_content' => dirname(__FILE__).'/admin.tpl'
	)
);

// Assign the template contents to ADMIN_CONTENT
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');
?>
