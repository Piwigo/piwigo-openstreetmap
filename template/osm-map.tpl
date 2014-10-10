<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset={$CONTENT_ENCODING}" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta name="robots" content="noindex,nofollow" />
<title>{$GALLERY_TITLE}</title>
</head>
<body>

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
html, body {
	height: 98%;
	margin: 0;
}

#map {
	min-height: 100%;
}
{/literal}
{/html_style}

<span> <a href="{$HOME}">{$HOME_NAME}</a> <a href="{$HOME_PREV}">{$HOME_PREV_NAME}</a> - <b id="nb_showall">{$TOTAL}</b> - <a id="showall" target="_blank" href="" style="display: none">{'ITEMS_SCREEN'|@translate}</a><span id='shownothing'>{'MOUSE_OVER'|@translate}</span></span>
<div id="map"></div>
<script type="text/javascript">{$OSMJS}</script>

<script type="text/javascript">
{literal}
	map.on('moveend', onMapMove);

	function onMapMove(e){
		getMarkers();
	}

	function getMarkers(){
		//var center = map.getCenter();
		//var zoom = map.getZoom();
		var bounds = map.getBounds();
		//console.log(bounds);
		
		var min = bounds.getSouthWest().wrap();
		var max = bounds.getNorthEast().wrap();

		var myurl = "{/literal}{$HOME}{literal}osmmap.php?min_lat="+min.lat+"&min_lng="+min.lng+"&max_lat="+max.lat+"&max_lng="+max.lng;
		//console.log(myurl);
		document.getElementById("showall").setAttribute('href',myurl);
		document.getElementById("shownothing").style.display = 'none';
		document.getElementById("showall").style.display = 'inline';

		var nb_items = 0;
		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];

			//console.log(a[0] +" > "+ min.lat +" && "+ a[1] +" > "+ min.lng +" && "+ a[0] +" < "+ max.lat +" && "+ a[1] +" < "+ max.lng);
			if (a[0] > min.lat && a[1] > min.lng && a[0] < max.lat && a[1] < max.lng)
			{
				//console.log("Inside bounds");
				nb_items++;
			}
		}
		document.getElementById("nb_showall").innerHTML = nb_items +' items';
	}
{/literal}
</script>

{get_combined_scripts load='footer'}
</body>
</html>
