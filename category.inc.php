<?php
/***********************************************
* File      :   category.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display an OSM map on the category layout
*
* Created   :   10.10.2014
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

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// Do we have to show the right panel
if ($conf['osm_conf']['category_description']['enabled'])
{
    // Hook to add comment
    add_event_handler('loc_begin_index', 'osm_render_category');
    // Hook to show data after Thumbnails
    // Use only one trigger as we do have all the data need on first run trigger
    // add_event_handler('loc_end_index', 'osm_render_category');
}

function osm_render_category()
{
        global $template, $page, $conf, $filter;

        include_once( dirname(__FILE__) .'/include/functions.php');
        include_once( dirname(__FILE__) .'/include/functions_map.php');
        osm_load_language();
        load_language('plugin.lang', OSM_PATH);

        $js_data = osm_get_items($page);
        if ($js_data != array())
        {
            $local_conf = array();
            $local_conf['contextmenu'] = 'false';
            $local_conf['control'] = true;
            $local_conf['img_popup'] = false;
            $local_conf['popup'] = 1;
            $local_conf['center_lat'] = 0;
            $local_conf['center_lng'] = 0;
            $local_conf['zoom'] = 2;
            $local_conf['autocenter'] = 1;
            $local_conf['paths'] = osm_get_gps($page);
            $height = isset($conf['osm_conf']['category_description']['height']) ? $conf['osm_conf']['category_description']['height'] : '200';
            $width = isset($conf['osm_conf']['category_description']['width']) ? $conf['osm_conf']['category_description']['width'] : 'auto';
            $js = osm_get_js($conf, $local_conf, $js_data);
            $template->set_filename('map', dirname(__FILE__).'/template/osm-category.tpl' );
            $template->assign(
                array(
                    'CONTENT_ENCODING' => get_pwg_charset(),
                    'OSM_PATH'         => embellish_url(get_gallery_home_url().OSM_PATH),
                    'HOME'             => make_index_url(),
                    'HOME_PREV'        => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : get_absolute_root_url(),
                    'HOME_NAME'        => l10n("Home"),
                    'HOME_PREV_NAME'   => l10n("Previous"),
                    'OSMJS'            => $js,
                    'HEIGHT'           => $height,
                    'WIDTH'            => $width,
                )
            );

            $osm_content = $template->parse('map', true);
            //$osm_content = '<div id="osmmap"><div class="map_title">'.l10n('EDIT_MAP').'</div>' . $osm_content . '</div>';
            $index = isset($conf['osm_conf']['category_description']['index']) ? $conf['osm_conf']['category_description']['index'] : 0;
            // 0 - PLUGIN_INDEX_CONTENT_BEGIN
            // 1 - PLUGIN_INDEX_CONTENT_COMMENT
            // 2 - PLUGIN_INDEX_CONTENT_END
            if ($index <= 1)
            {
              // From index category comment at L300
              if ($page['start']==0 and !isset($page['chronology_field']) )
              {
                if (empty($page['comment']))
                   $page['comment'] = $osm_content;
                else
                {
                  if ($index == 0)
                     $page['comment'] = '<div>' . $osm_content . $page['comment'] .'</div>';
                  else
                     $page['comment'] = '<div>' . $page['comment'] . $osm_content . '</div>';
                }
              }
            }
	    else
            {
              $osm_content = '<div id="osmmap">'. $osm_content . '</div>';
              $template->concat( 'PLUGIN_INDEX_CONTENT_END' , "\n".$osm_content);
            }
        }
}
