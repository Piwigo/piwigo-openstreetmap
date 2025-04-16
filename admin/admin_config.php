<?php
/***********************************************
* File      :   admin_config.php
* Project   :   piwigo-openstreetmap
* Descr     :   Install / Uninstall method
*
* Created   :   28.05.2013
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
    'toner'         => 'Stamen Toner',
    'custom'        => 'Own tile (custom style)',
    'esri'          => 'Esri.WorldImagery',
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

// Available options
// 0 - PLUGIN_INDEX_CONTENT_BEGIN
// 1 - PLUGIN_INDEX_CONTENT_COMMENT
// 2 - PLUGIN_INDEX_CONTENT_END
$available_cat_index = array(
    '0' => l10n('Thumbnail'),
    '1' => l10n('Description'),
    '2' => l10n('Last'),
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
    '3' => 'osm-map3.tpl',
//    '4' => 'osm-map4.tpl',
);

$forbidden = get_sql_condition_FandF(
  array
  (
      'forbidden_categories' => 'ic.category_id',
      'visible_categories' => 'ic.category_id',
      'visible_images' => 'i.id'
  ),
  "\n AND"
);

$INNER_JOIN = "INNER JOIN ".CATEGORIES_TABLE." AS c ON ic.category_id = c.id";

$query="SELECT i.path, i.name, i.id FROM ".IMAGES_TABLE." AS i
INNER JOIN (".IMAGE_CATEGORY_TABLE." AS ic ".$INNER_JOIN.") ON i.id = ic.image_id
WHERE `path` LIKE '%.gpx' ".$forbidden." ";

$available_gpx = array();

$result = pwg_query($query);
while ($row = pwg_db_fetch_array($result))
{
  $available_gpx[ $row['id'] ] = array(
    "id" => $row['id'],
    "name" => $row['name'],
  );
}

$query = 'SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE `latitude` IS NOT NULL and `longitude` IS NOT NULL ';
list($nb_geotagged) = pwg_db_fetch_array( pwg_query($query) );

// Update conf if submitted in admin site
if (isset($_POST['submit']) && !empty($_POST['osm_height']))
{
	// Check the center GPS position is valid
        $osm_left_center = (isset($_POST['osm_left_center']) and strlen($_POST['osm_left_center']) != 0) ? $_POST['osm_left_center'] : '0,0';
        $center_arr = explode(',', $osm_left_center);
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

  //Lists
  check_input_parameter($_POST['osm_baselayer'], $_POST, false, '/^('.implode('|', array_keys($available_baselayer)).')$/');
  check_input_parameter($_POST['osm_zoom'], $_POST, false, '/^('.implode('|', array_keys($available_zoom)).')$/');
  check_input_parameter($_POST['osm_add_before'], $_POST, false, '/^('.implode('|', array_keys($available_add_before)).')$/');
  check_input_parameter($_POST['osm_cat_index'], $_POST, false, '/^('.implode('|', array_keys($available_cat_index)).')$/');
  check_input_parameter($_POST['osm_pin'], $_POST, false, '/^('.implode('|', array_keys($available_pin)).')$/');
  check_input_parameter($_POST['osm_left_popup'], $_POST, false, '/^('.implode('|', array_keys($available_popup)).')$/');
  check_input_parameter($_POST['osm_left_layout'], $_POST, false, '/^('.implode('|', array_keys($available_layout)).')$/');
  check_input_parameter($_POST['osm_left_zoom'], $_POST, false, '/^('.implode('|', array_keys($available_zoom)).')$/');
  check_input_parameter($_POST['osm_display_gpx'], $_POST, false, '/^('.implode('|', array_keys($available_gpx)).')$/');

  //Numbers
  $integers = array(
    'osm_height',
    'osm_cat_height',
    'osm_menu_height',
    'osm_gpx_height',
    'osm_gpx_width',
    'osm_batch_global_height',
    'osm_batch_unit_height'
  );

  foreach ($integers as $integer)
  {
    check_input_parameter($integer, $_POST, false, '/^\d+$/');
  }

  check_input_parameter($_POST['osm_cat_width'], $_POST, false, '^auto$|^\d+$');

  //Strings

  check_input_parameter($_POST['osm_right_linkcss'], $_POST, false, '/^.{0,60}$/');
  check_input_parameter($_POST['osm_right_linkcss'], $_POST, false, '/^.{0,60}$/');

  check_input_parameter($_POST['osm_custombaselayer'], $_POST, false, '/^.{0,40}$/');
  check_input_parameter($_POST['osm_custombaselayerurl'], $_POST, false, '/^.{0,40}$/');
  check_input_parameter($_POST['osm_pinpath'], $_POST, false, '/^.{0,40}$/');
  check_input_parameter($_POST['osm_pinshadowpath'], $_POST, false, '/^.{0,40}$/');

  check_input_parameter($_POST['osm_right_link'], $_POST, false, '/^.{0,20}$/');
  check_input_parameter($_POST['osm_left_link'], $_POST, false, '/^.{0,20}$/');

  check_input_parameter($_POST['osm_pinsize'], $_POST, false, '/^.{0,6}$/');

  check_input_parameter($_POST['osm_pinshadowsize'], $_POST, false, '/^.{0,4}$/');
  check_input_parameter($_POST['osm_pinoffset'], $_POST, false, '/^.{0,4}$/');
  check_input_parameter($_POST['osm_pinpopupoffset'], $_POST, false, '/^.{0,4}$/');

  //Booleans
  $boolean_fields = array(
    'osm_right_panel',
    'osm_showosm',
    'osm_showlatlon',
    'osm_left_menu',
    'osm_left_autocenter',
    'osm_category_description',
    'osm_main_menu',
    'osm_noworldwarp',
    'osm_attrleaflet',
    'osm_attrimagery',
    'osm_attrplugin',
    'osm_community_bm',
    'osm_left_popupinfo_name',
    'osm_left_popupinfo_img',
    'osm_left_popupinfo_link',
    'osm_left_popupinfo_comment',
    'osm_left_popupinfo_author',
    'mapquestapi'
  );

  foreach ($boolean_fields as $boolean_field)
  {
    check_input_parameter($boolean_field, $_POST, false, '/^(true|false)$/');
  }
                
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
            'showlatlon'    => get_boolean($_POST['osm_showlatlon']),
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
            'center'            => $osm_left_center,
            'autocenter'        => get_boolean($_POST['osm_left_autocenter']),
            'layout'            => $_POST['osm_left_layout'],
			),
  'category_description' => array(
            'enabled'     => get_boolean($_POST['osm_category_description']),
            'height'      => $_POST['osm_cat_height'],
            'width'       => $_POST['osm_cat_width'],
            'index'       => $_POST['osm_cat_index'],
            'display_gpx' => $_POST['osm_display_gpx'],
      ),
	'main_menu' => array(
            'enabled' => get_boolean($_POST['osm_main_menu']),
            'height'  => $_POST['osm_menu_height'],
      ),
  'gpx' => array(
            'height' => $_POST['osm_gpx_height'],
            'width'  => $_POST['osm_gpx_width'],
      ),
  'batch' => array(
            'global_height' => $_POST['osm_batch_global_height'],
            'unit_height'  => $_POST['osm_batch_unit_height'],
      ),
	'map' => array(
            'baselayer'          => $_POST['osm_baselayer'],
            'custombaselayer'    => $_POST['osm_custombaselayer'],
            'custombaselayerurl' => $_POST['osm_custombaselayerurl'],
            'noworldwarp'        => get_boolean($_POST['osm_noworldwarp']),
            'attrleaflet'        => get_boolean($_POST['osm_attrleaflet']),
            'attrimagery'        => get_boolean($_POST['osm_attrimagery']),
            'attrplugin'         => get_boolean($_POST['osm_attrplugin']),
            'mapquestapi'        => isset($_POST['osm_mapquestapi']) ? $_POST['osm_mapquestapi'] : '',
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
  'community_bm' => array(
    'enabled' => isset($_POST['osm_community_bm']) ? get_boolean($_POST['osm_community_bm']) : '',
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
        'AVAILABLE_CAT_INDEX'  => $available_cat_index,
        'AVAILABLE_ZOOM'       => $available_zoom,
        'AVAILABLE_BASELAYER'  => $available_baselayer,
        'AVAILABLE_PIN'        => $available_pin,
        'AVAILABLE_POPUP'      => $available_popup,
        'AVAILABLE_LAYOUT'     => $available_layout,
        'AVAILABLE_GPX'        => $available_gpx,
        'NB_GEOTAGGED'         => $nb_geotagged,
        'OSM_PATH'             => OSM_PATH,
        'GLOBAL_MODE'          => l10n('global mode'),
        'SINGLE_MODE'          => l10n('unit mode'),
    )
);


$template->assign(
  array(
    'COMMUNITY_CONF' => isset($pwg_loaded_plugins['community']) ? $pwg_loaded_plugins['community'] : false,
  )
);


?>
