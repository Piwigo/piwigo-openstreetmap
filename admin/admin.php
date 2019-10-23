<?php
/***********************************************
* File      :   admin.php
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

include_once(PHPWG_ROOT_PATH.'admin/include/tabsheet.class.php');

// Add the template to the global template
$template->set_filename('plugin_admin_content', dirname(__FILE__).'/admin.tpl');

if (!isset($_GET['tab']))
  $page['tab'] = 'config';
else
  $page['tab'] = $_GET['tab'];

$my_base_url = get_admin_plugin_menu_link(__FILE__);

$tabsheet = new tabsheet();
$tabsheet->add( 'config', '<span class="icon-cog"></span>' . l10n('Configuration'), add_url_params( $my_base_url, array('tab'=>'config') ) );
$tabsheet->add( 'tag', '<span class="icon-tags"></span>' . l10n('Tags'), add_url_params( $my_base_url, array('tab'=>'tag') ) );
$tabsheet->add( 'place', '<span class="osm-location"></span>' . l10n('Places'), add_url_params( $my_base_url, array('tab'=>'place') ) );
$tabsheet->select($page['tab']);

$tabsheet->assign();

$my_base_url = $tabsheet->sheets[ $page['tab'] ]['url'];
$template->set_filename( 'tab_data', dirname(__FILE__).'/admin_'.$page['tab'].'.tpl' );
include_once( dirname(__FILE__).'/admin_'.$page['tab'].'.php');
$template->assign_var_from_handle( 'TAB_DATA', 'tab_data');
// Assign the template contents to ADMIN_CONTENT
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>
