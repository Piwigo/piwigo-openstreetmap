<?php
/***********************************************
* File      :   admin.php
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

// Available options
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

// Available pin
$available_pin = array(
	'0' => l10n('NOPIN'),
	'1' => l10n('DEFAULTPIN'),
	'2' => l10n('OWNPIN'),
);

// Available popup
$available_popup = array(
	'0' => l10n('CLICK'),
//	'1' => l10n('ALWAYS'),
	'2' => l10n('NEVER'),
);

// Available left popup
$available_popupinfo = array(
	'0' => l10n('NAME'),
	'1' => l10n('NAMETHUMB'),
	'2' => l10n('NAMETHUMBLINK'),
);

$query = 'SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE `lat` IS NOT NULL and `lon` IS NOT NULL ';
list($nb_geotagged) = pwg_db_fetch_array( pwg_query($query) );

// Update conf if submitted in admin site
if (isset($_POST['submit']) && !empty($_POST['osm_height']))
{
	// On post admin form
	$conf['osm_conf'] = array(
		'right_panel' => array(
			'enabled' 	=> get_boolean($_POST['osm_right_panel']),
			'add_before' 	=> $_POST['osm_add_before'],
			'height' 	=> $_POST['osm_height'],
			'zoom' 		=> $_POST['osm_zoom'],
			'link'          => $_POST['osm_right_link'],
			),
		'left_menu' => array(
			'enabled'       	=> get_boolean($_POST['osm_left_menu']),
			'link'          	=> $_POST['osm_left_link'],
			'popup'			=> $_POST['osm_left_popup'],
			'popupinfo_name'	=> isset($_POST['osm_left_popupinfo_name']),
			'popupinfo_img'		=> isset($_POST['osm_left_popupinfo_img']),
			'popupinfo_link'	=> isset($_POST['osm_left_popupinfo_link']),
			'popupinfo_comment'	=> isset($_POST['osm_left_popupinfo_comment']),
			'popupinfo_author'	=> isset($_POST['osm_left_popupinfo_author']),
			),
		'map' => array(
			'baselayer' 		=> $_POST['osm_baselayer'],
			'custombaselayer' 	=> $_POST['osm_custombaselayer'],
			'custombaselayerurl'	=> $_POST['osm_custombaselayerurl'],
			'noworldwarp' 		=> get_boolean($_POST['osm_noworldwarp']),
			'attrleaflet' 		=> get_boolean($_POST['osm_attrleaflet']),
			'attrimagery' 		=> get_boolean($_POST['osm_attrimagery']),
			'attrplugin' 		=> get_boolean($_POST['osm_attrplugin']),
			),
		'auto_sync' 		=> get_boolean($_POST['osm_auto_sync']),
	);

	// Update config to DB
	conf_update_param('osm_conf', serialize($conf['osm_conf']));

	// the prefilter changes, we must delete compiled templatess
	$template->delete_compiled_templates();
	array_push($page['infos'], l10n('Your configuration settings are saved'));
}

//print_r($conf['osm_conf']);

// send value to template
$template->assign($conf['osm_conf']);
$template->assign(
	array(
		'AVAILABLE_ADD_BEFORE'	=> $available_add_before,
		'AVAILABLE_ZOOM'	=> $available_zoom,
		'AVAILABLE_BASELAYER'	=> $available_baselayer,
		'AVAILABLE_PIN'		=> $available_pin,
		'AVAILABLE_POPUP'	=> $available_popup,
		'AVAILABLE_POPUPINFO'	=> $available_popupinfo,
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
