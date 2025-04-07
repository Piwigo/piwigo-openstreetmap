{html_head}
<link href="{$OSM_PATH}leaflet/leaflet.css" rel="stylesheet">
<script src="{$OSM_PATH}leaflet/leaflet.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet-omnivore.min.js"></script>
<link rel="stylesheet" href="{$OSM_PATH}leaflet/MarkerCluster.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/MarkerCluster.Default.css" />
<script src="{$OSM_PATH}leaflet/leaflet.markercluster.js"></script>
{/html_head}

{html_style}
{literal}
#map {
    height: {/literal}{$OSM_HEIGHT}{literal}px;
    width: {/literal}{$OSM_WIDTH}{literal}px;
    {/literal}{if $OSM_WIDTH != 'auto'}width: {$OSM_WIDTH}px;
    float: left;{/if}{literal}

}

#community_edit_photos #map,
#community_add_photos #map{
  display:none;
}
{/literal}
{/html_style}

<div id="map"></div>
<script type="text/javascript">{$OSMJS}

{literal}
    map.on('moveend', onMapMove);

    function onMapMove(e){
        getMarkers();
    }

    function getMarkers(){
        var bounds = map.getBounds();
    }
{/literal}
</script>

