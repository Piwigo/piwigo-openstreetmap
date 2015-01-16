<?php
/***********************************************
* File      :   picture.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display map on right panel
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

include_once( dirname(__FILE__) .'/include/functions_map.php');
// Do we have to show the right panel
if ($conf['osm_conf']['right_panel']['enabled'])
{
    // Hook to add the div in the right menu, No idea about the number!!
    add_event_handler('loc_begin_picture', 'osm_loc_begin_picture', 56);

    // Hook to populate the div in the right menu, No idea about the number after!!
    add_event_handler('loc_begin_picture', 'osm_render_element_content', EVENT_HANDLER_PRIORITY_NEUTRAL+1 /*in order to have picture content*/, 2);
}

function osm_loc_begin_picture()
{
    global $template;
    $template->set_prefilter('picture', 'osm_insert_map');
}

function osm_insert_map($content, &$smarty)
{
    global $conf;
    load_language('plugin.lang', OSM_PATH);

/*	Would be better if it could be like the Metdata but how?
	$search = '#<dl id="Metadata" class="imageInfoTable">#';
	$replacement = '
<dl id="map-info" class="imageInfoTable">
	<h3>{\'LOCATION\'|@translate}</h3>
	<div class="imageInfo">
		<div id="map"></div>
	</div>
</dl>
<dl id="Metadata" class="imageInfoTable">';
*/

    $search = '#<div id="'. $conf['osm_conf']['right_panel']['add_before'] .'" class="imageInfo">#';
    $replacement = '
{if $OSMJS}
<div id="map-info" class="imageInfo">
    <dt {$OSMNAMECSS}>{$OSMNAME}</dt>
    <dd>
        <div id="map"></div>
        <script type="text/javascript">{$OSMJS}</script>
        <div id="osm_attrib" style="visibility: hidden; display: none;">
            <ul>
                <li>{"PLUGIN_BY"|@translate}</li>
                <li><a href="http://leafletjs.com/" target="_blank">Leaflet</a></li>
                <li>&copy; {"OSM_CONTRIBUTORS"|@translate}</li>
            </ul>
        </div>
        {if $SHOWOSM}
        <a href="{$OSMLINK}" target="_blank">{"VIEW_OSM"|@translate}</a>
        {/if}
    </dd>
</div>
{/if}
<div id="'. $conf['osm_conf']['right_panel']['add_before'] .'" class="imageInfo">';

    return preg_replace($search, $replacement, $content);
}

function osm_render_element_content()
{
    global $template, $picture, $page, $conf;
    load_language('plugin.lang', OSM_PATH);

    if (empty($page['image_id']))
    {
        return;
    }

    // Load coordinates from picture
    $query = 'SELECT latitude,longitude FROM '.IMAGES_TABLE.' WHERE id = \''.$page['image_id'].'\' ;';
    //FIXME LIMIT 1 ?
    $result = pwg_query($query);
    $row = pwg_db_fetch_assoc($result);
    if (!$row or !$row['latitude'] or empty($row['latitude']))
    {
        return;
    }
    $lat = $row['latitude'];
    $lon = $row['longitude'];

    // Load parameter, fallback to default if unset
    $height = isset($conf['osm_conf']['right_panel']['height']) ? $conf['osm_conf']['right_panel']['height'] : '200';
    $zoom = isset($conf['osm_conf']['right_panel']['zoom']) ? $conf['osm_conf']['right_panel']['zoom'] : '12';
    $osmname = isset($conf['osm_conf']['right_panel']['link']) ? $conf['osm_conf']['right_panel']['link'] : 'Location';
    $osmnamecss = isset($conf['osm_conf']['right_panel']['linkcss']) ? $conf['osm_conf']['right_panel']['linkcss'] : '';
    $showosm = isset($conf['osm_conf']['right_panel']['showosm']) ? $conf['osm_conf']['right_panel']['showosm'] : 'true';
    if (strlen($osmnamecss) != 0)
    {
        $osmnamecss = "style='".$osmnamecss."'";
    }
    $osmlink="https://openstreetmap.org/?mlat=".$lat."&amp;mlon=".$lon."&zoom=12&layers=M";

    $local_conf = array();
    $local_conf['contextmenu'] = 'false';
    $local_conf['control'] = false;
    $local_conf['img_popup'] = false;
    $local_conf['popup'] = 2;
    $local_conf['center_lat'] = $lat;
    $local_conf['center_lng'] = $lon;
    $local_conf['zoom'] = $zoom;

    $js_data = array(array($lat, $lon, null, null, null, null, null, null));

    $js = osm_get_js($conf, $local_conf, $js_data);

    // Select the template
    $template->set_filenames(
            array('osm_content' => dirname(__FILE__)."/template/osm-picture.tpl")
    );

    // Assign the template variables
    $template->assign(
        array(
            'HEIGHT'		=> $height,
            'OSMJS' 		=> $js,
            'OSM_PATH'		=> embellish_url(get_absolute_root_url().OSM_PATH),
            'OSMNAME'		=> $osmname,
            'OSMNAMECSS'	=> $osmnamecss,
            'SHOWOSM'		=> $showosm,
            'OSMLINK'		=> $osmlink,
        )
    );

    // Return the rendered html
    $osm_content = $template->parse('osm_content', true);
    return $osm_content;
}
