<?php
/***********************************************
* File      :   menu.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display an OSM map on mainmenu right
*
* Created   :   10.10.2014
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

// Chech whether we are indeed included by Piwigo.
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

if ($conf['osm_conf']['main_menu']['enabled'])
{
    add_event_handler('blockmanager_register_blocks', 'osm_register_menu');
    add_event_handler('blockmanager_apply', 'osm_apply_menu');
}
function osm_register_menu( $menu_ref_arr )
{
    $menu = & $menu_ref_arr[0];
    if ($menu->get_id() != 'menubar')
        return;
    $menu->register_block( new RegisteredBlock( 'mbAbout', 'About', 'A1M'));
}

function osm_apply_menu($menu_ref_arr)
{
    global $template, $page, $conf;

    $menu = & $menu_ref_arr[0];

    if (($block = $menu->get_block('mbLinks')) != null) {
        include_once( dirname(__FILE__) .'/include/functions.php');
        include_once(dirname(__FILE__).'/include/functions_map.php');
        osm_load_language();
        load_language('plugin.lang', OSM_PATH);

        // Comment are used only with this condition index.php l294
        if ($page['start']==0 and !isset($page['chronology_field']) )
        {
            $js_data = osm_get_items($page);
            if ($js_data != array())
            {
                $local_conf = array();
                $local_conf['contextmenu'] = 'false';
                $local_conf['control'] = true;
                $local_conf['img_popup'] = false;
                $local_conf['popup'] = 2;
                $local_conf['center_lat'] = 0;
                $local_conf['center_lng'] = 0;
                $local_conf['zoom'] = 2;
                $local_conf['auto_center'] = 0;
                $local_conf['divname'] = 'mapmenu';
                $local_conf['paths'] = osm_get_gps($page);
                $height = isset($conf['osm_conf']['main_menu']['height']) ? $conf['osm_conf']['main_menu']['height'] : '200';
                $js = osm_get_js($conf, $local_conf, $js_data);
                $template->set_template_dir(dirname(__FILE__).'/template/');
                $template->assign(
                    array(
                        'OSM_PATH' => embellish_url(get_absolute_root_url().OSM_PATH),
                        'OSMJS'    => $js,
                        'HEIGHT'   => $height,
                    )
                );
                $block->template = 'osm-menu.tpl';
            }
        }
    }
}
?>
