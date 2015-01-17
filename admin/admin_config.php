<?php
/***********************************************
* File      :   admin_config.php
* Project   :   piwigo-openstreetmap
* Descr     :   Install / Uninstall method
*
* Created   :   28.05.2013
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
    'mapnik'        => 'OpenStreetMap Mapnik',
    'blackandwhite' => 'OpenStreetMap BlackAndWhite',
    'mapnikfr'      => 'OpenStreetMap FR',
    'mapnikde'      => 'OpenStreetMap DE',
    'mapnikhot'     => 'OpenStreetMap HOT',
    'mapquest'      => 'MapQuestOpen',
    'mapquestaerial'=> 'MapQuestOpen Aerial',
    'cloudmade'     => 'Cloudmade',
    'custom'        => 'Own tile (custom style)',
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
    '10'=> '10',
    '11'=> '11',
    '12'=> '12',
    '13'=> '13',
    '14'=> '14',
    '15'=> '15',
    '16'=> '16',
    '17'=> '17',
    '18'=> '18',
);

// Available options
$available_add_before = array(
    'Author'    => l10n('Author'),
    'datecreate'=> l10n('Created on'),
    'datepost'  => l10n('Posted on'),
    'Dimensions'=> l10n('Dimensions'),
    'File'      => l10n('File'),
    'Filesize'  => l10n('Filesize'),
    'Tags'      => l10n('Tags'),
    'Categories'=> l10n('Albums'),
    'Visits'    => l10n('Visits'),
    'Average'   => l10n('Rating score'),
    'rating'    => l10n('Rate this photo'),
    'Privacy'   => l10n('Who can see this photo?'),
);

// Available pin
$available_pin = array(
    '0' => l10n('NOPIN'),
    '1' => l10n('DEFAULTPIN'),
    '2' => l10n('DEFAULTPINGREEN'),
    '3' => l10n('DEFAULTPINRED'),
    '4' => l10n('LEAFPINGREEN'),
    '5' => l10n('LEAFPINORANGE'),
    '6' => l10n('LEAFPINRED'),
    '7' => l10n('MAPICONSBLEU'),
    '8' => l10n('MAPICONSGREEN'),
    '9' => l10n('OWNPIN'),
    '10'=> l10n('IMAGE'),
);

// Available popup
$available_popup = array(
    '0' => l10n('CLICK'),
//    '1' => l10n('ALWAYS'),
    '2' => l10n('NEVER'),
);

// Available layout value
$available_layout = array(
    '1' => 'osm-map.tpl',
    '2' => 'osm-map2.tpl',
//    '3' => 'osm-map3.tpl',
);

$query = 'SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE `latitude` IS NOT NULL and `longitude` IS NOT NULL ';
list($nb_geotagged) = pwg_db_fetch_array( pwg_query($query) );

// Update conf if submitted in admin site
if (isset($_POST['submit']) && !empty($_POST['osm_height']))
{
	// Check the center GPS position is valid
	if (isset($_POST['osm_left_center']) and strlen($_POST['osm_left_center']) != 0)
        $center_arr = explode(',', $_POST['osm_left_center']);
        //print_r($center_arr);
        $latitude = $center_arr[0];
        $longitude = $center_arr[1];
        if (isset($latitude) and isset($longitude))
            if ( strlen($latitude)==0 and strlen($longitude)==0 )
                array_push($page['warnings'], l10n('Both latitude/longitude must not empty'));
            if (isset($latitude) and ($latitude <= -90 or $latitude >= 90))
                array_push($page['warnings'], l10n('The specify center latitude (-90=S to 90=N) is not valid'));
            if (isset($longitude) and ($longitude <= -180 or $longitude >= 180))
                array_push($page['warnings'], l10n('The specify center longitude (-180=W to 180=E) is not valid'));

	// On post admin form
	$conf['osm_conf'] = array(
		'right_panel' => array(
            'enabled'    => get_boolean($_POST['osm_right_panel']),
            'add_before' => $_POST['osm_add_before'],
            'height'     => $_POST['osm_height'],
            'zoom'       => $_POST['osm_zoom'],
            'link'       => $_POST['osm_right_link'],
            'linkcss'    => $_POST['osm_right_linkcss'],
            'showosm'    => get_boolean($_POST['osm_showosm']),
			),
		'left_menu' => array(
            'enabled'           => get_boolean($_POST['osm_left_menu']),
            'link'              => $_POST['osm_left_link'],
            'popup'             => $_POST['osm_left_popup'],
            'popupinfo_name'    => isset($_POST['osm_left_popupinfo_name']),
            'popupinfo_img'     => isset($_POST['osm_left_popupinfo_img']),
            'popupinfo_link'    => isset($_POST['osm_left_popupinfo_link']),
            'popupinfo_comment' => isset($_POST['osm_left_popupinfo_comment']),
            'popupinfo_author'  => isset($_POST['osm_left_popupinfo_author']),
            'zoom'              => $_POST['osm_left_zoom'],
            'center'            => $_POST['osm_left_center'],
            'layout'            => $_POST['osm_left_layout'],
			),
        'category_description' => array(
            'enabled' => get_boolean($_POST['osm_category_description']),
            'height'  => $_POST['osm_cat_height'],
            'width'   => $_POST['osm_cat_width'],
            ),
		'main_menu' => array(
            'enabled' => get_boolean($_POST['osm_main_menu']),
            'height'  => $_POST['osm_menu_height'],
            ),
        'gpx' => array(
            'height' => $_POST['osm_gpx_height'],
            'width'  => $_POST['osm_gpx_width'],
            ),
		'map' => array(
            'baselayer'          => $_POST['osm_baselayer'],
            'custombaselayer'    => $_POST['osm_custombaselayer'],
            'custombaselayerurl' => $_POST['osm_custombaselayerurl'],
            'noworldwarp'        => get_boolean($_POST['osm_noworldwarp']),
            'attrleaflet'        => get_boolean($_POST['osm_attrleaflet']),
            'attrimagery'        => get_boolean($_POST['osm_attrimagery']),
            'attrplugin'         => get_boolean($_POST['osm_attrplugin']),
			),
		'pin' => array(
            'pin'            => $_POST['osm_pin'],
            'pinpath'        => $_POST['osm_pinpath'],
            'pinsize'        => $_POST['osm_pinsize'],
            'pinshadowpath'  => $_POST['osm_pinshadowpath'],
            'pinshadowsize'  => $_POST['osm_pinshadowsize'],
            'pinoffset'      => $_POST['osm_pinoffset'],
            'pinpopupoffset' => $_POST['osm_pinpopupoffset'],
			),
	);

    // Update config to DB
    conf_update_param('osm_conf', serialize($conf['osm_conf']));

    // the prefilter changes, we must delete compiled templatess
    $template->delete_compiled_templates();
    array_push($page['infos'], l10n('Your configuration settings are saved'));
}

// send value to template
$template->assign($conf['osm_conf']);
$template->assign(
    array(
        'AVAILABLE_ADD_BEFORE' => $available_add_before,
        'AVAILABLE_ZOOM'       => $available_zoom,
        'AVAILABLE_BASELAYER'  => $available_baselayer,
        'AVAILABLE_PIN'        => $available_pin,
        'AVAILABLE_POPUP'      => $available_popup,
        'AVAILABLE_LAYOUT'     => $available_layout,
        'NB_GEOTAGGED'         => $nb_geotagged,
        'OSM_PATH'             => OSM_PATH,
    )
);

?>
