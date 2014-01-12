<?php
/***********************************************
* File      :   picture.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Display map on right panel
*
* Created   :   28.05.2013
*
* Copyright 2013-2014 <xbgmsharp@gmail.com>
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

/*	Would be better if you could be like the Metdata but how?
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
	{if $SHOWOSM}
        View on <a href="{$OSMLINK}" target="_blank">OpenStreetMap</a>
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
    $result = pwg_query($query);
    $row = pwg_db_fetch_assoc($result);
    if (!$row or !$row['latitude'] or empty($row['latitude'])) { return; }
    $lat = $row['latitude'];
    $lon = $row['longitude'];

    // Load parameter, fallback to default if unset
    $height = isset($conf['osm_conf']['right_panel']['height']) ? $conf['osm_conf']['right_panel']['height'] : '200';
    $zoom = isset($conf['osm_conf']['right_panel']['zoom']) ? $conf['osm_conf']['right_panel']['zoom'] : '12';
    $osmname = isset($conf['osm_conf']['right_panel']['link']) ? $conf['osm_conf']['right_panel']['link'] : 'Location';
    $osmnamecss = isset($conf['osm_conf']['right_panel']['linkcss']) ? $conf['osm_conf']['right_panel']['linkcss'] : '';
    $showosm = isset($conf['osm_conf']['right_panel']['showosm']) ? $conf['osm_conf']['right_panel']['showosm'] : 'true';
    $baselayer = isset($conf['osm_conf']['map']['baselayer']) ? $conf['osm_conf']['map']['baselayer'] : 'mapnik';
    $custombaselayer = isset($conf['osm_conf']['map']['custombaselayer']) ? $conf['osm_conf']['map']['custombaselayer'] : '';
    $custombaselayerurl = isset($conf['osm_conf']['map']['custombaselayerurl']) ? $conf['osm_conf']['map']['custombaselayerurl'] : '';
    $noworldwarp = isset($conf['osm_conf']['map']['noworldwarp']) ? $conf['osm_conf']['map']['noworldwarp'] : 'false';
    $attrleaflet = isset($conf['osm_conf']['map']['attrleaflet']) ? $conf['osm_conf']['map']['attrleaflet'] : 'false';
    $attrimagery = isset($conf['osm_conf']['map']['attrimagery']) ? $conf['osm_conf']['map']['attrimagery'] : 'false';
    $attrmodule = isset($conf['osm_conf']['map']['attrplugin']) ? $conf['osm_conf']['map']['attrplugin'] : 'false';

    if (strlen($osmnamecss) != 0)
    {
	$osmnamecss = "style='".$osmnamecss."'";
    }

    $osmlink="http://openstreetmap.org/?mlat=".$lat."&amp;mlon=".$lon."&zoom=12&layers=M";

    // Load baselayerURL
    // Key1 BC9A493B41014CAABB98F0471D759707
	if     ($baselayer == 'mapnik')		$baselayerurl = 'http://tile.openstreetmap.org/{z}/{x}/{y}.png';
	else if($baselayer == 'mapquest')	$baselayerurl = 'http://otile1.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png';
	else if($baselayer == 'cloudmade')	$baselayerurl = 'http://{s}.tile.cloudmade.com/7807cc60c1354628aab5156cfc1d4b3b/997/256/{z}/{x}/{y}.png';
	else if($baselayer == 'mapnikde')	$baselayerurl = 'http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png';
	else if($baselayer == 'mapnikfr')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png';
	else if($baselayer == 'blackandwhite')	$baselayerurl = 'http://{s}.www.toolserver.org/tiles/bw-mapnik/{z}/{x}/{y}.png';
	else if($baselayer == 'mapnikhot')	$baselayerurl = 'http://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png';
	else if($baselayer == 'mapquestaerial')	$baselayerurl = 'http://oatile1.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.jpg';
	else if($baselayer == 'custom')	$baselayerurl = $custombaselayerurl;

	$attribution = osmcopyright($attrleaflet, $attrimagery, $attrmodule, $baselayer, $custombaselayer);

    // Generate Javascript
    // ----------------------------------------
    // no worldWarp (no world copies, restrict the view to one world)
    if($noworldwarp)
    {
	$nowarp = " true ";
	$worldcopyjump = "worldCopyJump: false, maxBounds: [ [82, -180], [-82, 180] ]";
    }
    else
    {
	$nowarp = " false ";
	$worldcopyjump = "worldCopyJump: true";
    }

/*
    // Icons
    $js = "\n
var LeafIcon = L.Icon.extend({
    options: {
	shadowUrl: 'plugins/piwigo-openstreetmap/leaflet/images/leaf-shadow.png',
	iconSize:     [38, 95],
	shadowSize:   [50, 64],
	iconAnchor:   [22, 94],
	shadowAnchor: [4, 62],
	popupAnchor:  [-3, -76]
    }
});

var mapIcon = L.Icon.extend({
    options: {
	shadowUrl: 'plugins/piwigo-openstreetmap/leaflet/images/mapicons-shadow.png',
	iconSize:     [32, 37],
	shadowSize:   [51, 37],
	iconAnchor:   [19, 38],
	shadowAnchor: [-20, 33],
	popupAnchor:  [-2, -10]
    }
});

var greenIcon = new LeafIcon({iconUrl: 'plugins/piwigo-openstreetmap/leaflet/images/leaf-green.png'}),
	redIcon = new LeafIcon({iconUrl: 'plugins/piwigo-openstreetmap/leaflet/images/leaf-red.png'}),
	orangeIcon = new LeafIcon({iconUrl: 'plugins/piwigo-openstreetmap/leaflet/images/leaf-orange.png'});

var bluemapicons = new mapIcon({iconUrl: 'plugins/piwigo-openstreetmap/leaflet/images/mapicons-blue.png'}),
	greenmapicons = new mapIcon({iconUrl: 'plugins/piwigo-openstreetmap/leaflet/images/mapicons-green.png'});
";
*/
    // Create the map and get a new map instance attached and element with id="tile-map"
    $js = "\nvar map = new L.Map('map', {".$worldcopyjump."});\n";
    $js .= "map.attributionControl.setPrefix('');\n";
    $js .= "var baselayer = new L.TileLayer('".$baselayerurl."', {maxZoom: 18, noWrap: ".$nowarp.", attribution: '".$attribution."'});\n";
    $js .= "var coord = new L.LatLng(".$lat.", ".$lon.");\n";
    $js .= "var marker = new L.Marker(coord);\n";
    //$js .= "var marker = new L.Marker(coord, {icon: bluemapicons});\n";
    $js .= "map.addLayer(marker);\n";

    // set map view
    $js .= "map.setView(coord, ".$zoom.").addLayer(baselayer);\n";

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
