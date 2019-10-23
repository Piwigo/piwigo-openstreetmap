<!DOCTYPE html>
<html>{html_head}
<meta http-equiv="content-type" content="text/html; charset={$CONTENT_ENCODING}" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<meta name="robots" content="noindex,nofollow" />
<title>{$GALLERY_TITLE}</title>
<link rel="stylesheet" href="{$OSM_PATH}fontello/css/osm.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/leaflet.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/leaflet-search.min.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/MarkerCluster.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/MarkerCluster.Default.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/leaflet.contextmenu.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/Control.MiniMap.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/L.Control.ViewCenter.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/L.Control.Sidebar.css" />
<script src="{$OSM_PATH}leaflet/leaflet.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet-search.min.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet.markercluster.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet.contextmenu.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet-omnivore.min.js"></script>
<script src="{$OSM_PATH}leaflet/Control.MiniMap.js"></script>
<script src="{$OSM_PATH}leaflet/L.Control.ControlCenter.js"></script>
<script src="{$OSM_PATH}leaflet/L.Control.ViewCenter.js"></script>
<script src="{$OSM_PATH}leaflet/L.Control.Sidebar.js"></script>
{html_style}
{literal}
html, body {
	height: 100%;
	width: 100%;
	margin: 0;
	padding: 0;
}

#map {
	position: absolute;
	top:0;
	left:0;
	right:0;
	bottom:0;
}
/*
.leaflet-bottom {
    transition: bottom 0.5s;
}
.leaflet-right {
    transition: bottom 0.5s;
}
#leaflet-bar-up a {
	//float: right;
}

#sidebar-up {
	//transition: bottom 0.8s ease 0s, height 0.8s ease 0s;
	//transition: background-color 0.8s ease;
	//background-color: red;
	//bottom: 0;
	//right: 0;
}
#sidebar-up.visible {
	//background-color: green;
	//width: 100%;
	//height: 100px;
	//bottom: 0;
	//right: 0;
}

#sidebar-up.bottom.visible ~ .leaflet-bottom {
	height: 190px;
}
#sidebar-up.bottom.visible ~ .leaflet-left {
	height: 140px;
}
#sidebar-up.left.visible ~ .leaflet-left {
	left: 0px;
}
*/

/* Tiny Scrollbar */
#scrollbar1
{
    height:150px;
    width:100%;
    margin:0 0 10px;
}

#scrollbar1 .viewport
{
    width:236px;
    height:125px;
    overflow:hidden;
    position:relative;
}

#scrollbar1 .overview
{
    list-style:none;
    width:1416px;
    padding:0;
    margin:0;
    position:absolute;
    left:0;
    top:0;
}

#scrollbar1 .overview img
{
    float:left;
}

#scrollbar1 .scrollbar
{
    background:transparent url(plugins/piwigo-openstreetmap/leaflet/images/bg-scrollbar-track-y.png) no-repeat 0 0;
    position:relative;
    margin:0 0 5px;
    clear:both;
    height:15px;
}

#scrollbar1 .track
{
    background:transparent url(plugins/piwigo-openstreetmap/leaflet/images/bg-scrollbar-trackend-y.png) no-repeat 100% 0;
    width:100%;
    height:15px;
    position:relative;
}

#scrollbar1 .thumb
{
    background:transparent url(plugins/piwigo-openstreetmap/leaflet/images/bg-scrollbar-thumb-y.png) no-repeat 100% 50%;
    height:25px;
    cursor:pointer;
    overflow:hidden;
    position:absolute;
    left:0;
    top:-5px;
}

#scrollbar1 .thumb .end
{
    background:transparent url(plugins/piwigo-openstreetmap/leaflet/images/bg-scrollbar-thumb-y.png) no-repeat 0 50%;
    overflow:hidden;
    height:25px;
    width:5px;
}

#scrollbar1 .disable
{
    display:none;
}

.noSelect
{
    user-select:none;
    -o-user-select:none;
    -moz-user-select:none;
    -khtml-user-select:none;
    -webkit-user-select:none;
}

#dialog {
	font-family: Arial,Helvetica,sans-serif;
	font-size: 10px;
	text-align: center;
	text-decoration: none;
}
{/literal}{/html_style}{/html_head}
</head>
<body>
<noscript>Your browser must have JavaScript enable</noscript> 

<div id="sidebar-left">
    <h1>leaflet-sidebar</h1>
</div>

<div id="scrollbar1">
	<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
	<div class="viewport">
		<div class="overview">
			<img src="http://placehold.it/100x40&text=FooBar1" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar2" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar3" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar4" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar5" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar6" alt="image 2" />
			<img src="http://placehold.it/100x40&text=FooBar1" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar2" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar3" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar4" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar5" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar6" alt="image 1" />
			<img src="http://placehold.it/100x40&text=FooBar1" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar2" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar3" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar4" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar5" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar6" alt="image 3" />
			<img src="http://placehold.it/100x40&text=FooBar1" alt="image 4" />
			<img src="http://placehold.it/100x40&text=FooBar2" alt="image 4" />
			<img src="http://placehold.it/100x40&text=FooBar3" alt="image 4" />
			<img src="http://placehold.it/100x40&text=FooBar4" alt="image 4" />
			<img src="http://placehold.it/100x40&text=FooBar5" alt="image 4" />
			<img src="http://placehold.it/100x40&text=FooBar6" alt="image 4" />
		</div>
	</div>
</div>

<div id="map"></div>

<div id="dialog" title="Link to this map">
	<p>Copy and Paste the URL below:</p>
	<input type="text" value="" style="width: 550px;" onfocus="this.select();" id="textfield"></input>
</div>

<script type="text/javascript">{$OSMJS}</script>

<script type="text/javascript">
{literal}

	/* Load leaflet PWG-OSM ControlCenter Leaflet plugin */
	map.addControl( new L.Control.ControlCenter() );

	/* Load leaflet PWG-OSM ViewCenter Leaflet plugin */
	var viewcenter = new L.Control.ViewCenter()
	map.addControl( viewcenter );

	function ShowThumbs() {
		viewcenter.toggle();
	}

	/* BEGIN leaflet-MiniMap https://github.com/Norkart/Leaflet-MiniMap */
	var osm2 = new L.TileLayer(Url, {minZoom: 0, maxZoom: 13, attribution: Attribution});
	var miniMap = new L.Control.MiniMap(osm2).addTo(map);
	/* END leaflet-MiniMap */

	/* BEGIN */
	var sidebar = L.control.sidebar('sidebar-left', {
		position: 'left'
	});
	map.addControl(sidebar);

	function goShowInfo() {
		sidebar.toggle();
	}
	/* END L.control.sidebar */

	/* BEGIN leaflet-search https://github.com/stefanocudini/leaflet-search */
	var jsonpurl = 'https://open.mapquestapi.com/nominatim/v1/search.php?q={s}'+
				   '&format=json&osm_type=N&limit=100&addressdetails=0',
		jsonpName = 'json_callback';
	//third party jsonp service

	function filterJSONCall(rawjson) {	//callback that remap fields name
		var json = {},
			key, loc, disp = [];

		for(var i in rawjson)
		{
			disp = rawjson[i].display_name.split(',');
			key = disp[0] +', '+ disp[1];
			loc = L.latLng( rawjson[i].lat, rawjson[i].lon );
			json[ key ]= loc;	//key,value format
		}

		return json;
	}

	var searchOpts = {
			url: jsonpurl,
			jsonpParam: jsonpName,
			filterJSON: filterJSONCall,
			animateLocation: true,
			circleLocation: false,
			markerLocation: false,
			zoom: 12,
			minLength: 3,
			autoType: false,
		};

	map.addControl( new L.Control.Search(searchOpts) );

	/* END leaflet-search */
	/* https://github.com/codeforamerica/lv-trucks-map/blob/master/js/main.js */
	/* http://clvfoodtrucks.com/ */
	L.Map.prototype.panToOffset = function (latlng, offset, options) {
	  var x = this.latLngToContainerPoint(latlng).x - offset[0]
	  var y = this.latLngToContainerPoint(latlng).y - offset[1]
	  var point = this.containerPointToLatLng([x, y])
	  return this.setView(point, this._zoom, { pan: options })
	}

	/* BEGIN leaflet-contextmenu https://github.com/aratcliffe/Leaflet.contextmenu */
	function goHome (){
		window.location.assign('{/literal}{$HOME}{literal}');
	}

	function goBack (){
		window.location.assign('{/literal}{$HOME_PREV}{literal}');
	}

	function showCoordinates (e) {
		var popup = L.popup();
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(map);
	}

	function centerMap (e) {
		map.panTo(e.latlng);
		//getMarkers(); /* Center on Map is not consider as Move so we have to update the data ourself */
	}

	function goShowAll (e) {
		/* Get coordonates */
		var bounds = map.getBounds();
		var min = bounds.getSouthWest().wrap();
		var max = bounds.getNorthEast().wrap();

		/* Update ShowAll link */
		var root_url = '{/literal}{$MYROOT_URL}{literal}';
		var myurl = root_url+"osmmap.php?min_lat="+min.lat+"&min_lng="+min.lng+"&max_lat="+max.lat+"&max_lng="+max.lng;
		//console.log("ShowAll:"+myurl);
		window.open(myurl,'_blank');
	}

	function linkToThisMap (){
		var center = map.getCenter();
		var zoom = map.getZoom();

		var centerlat = center.lat;
		var centerlng = center.lng;

		var root_url = '{/literal}{$MYROOT_URL}{literal}';
		var myurl = root_url+"osmmap.php?zoom="+zoom+"&center_lat="+centerlat+"&center_lng="+centerlng;
		//console.log(myurl);
		document.getElementById('textfield').value = myurl;
		$('#dialog').dialog('open');
	}

	function findMyLocation (){
		/* http://leafletjs.com/examples/mobile-example.html */
		/* http://www.bennadel.com/blog/2023-Geocoding-A-User-s-Location-Using-Javascript-s-GeoLocation-API.htm */
		map.locate({setView: true, maxZoom: 16});
	}

	function zoomIn (e) {
		map.zoomIn();
	}

	function zoomOut (e) {
		map.zoomOut();
	}

	map.contextmenu.addItem({text: '{/literal}{$HOME_NAME}{literal}', iconCls: 'osm-home', callback: goHome});
	map.contextmenu.addItem({text: '{/literal}{$HOME_PREV_NAME}{literal}', iconCls: 'osm-left-big', callback: goBack});
	map.contextmenu.addItem('-');
	map.contextmenu.addItem({text: 'Show coordinates', iconCls: 'osm-pin', callback: showCoordinates});
	map.contextmenu.addItem({text: 'Center map here', iconCls: 'osm-location', callback: centerMap});
	map.contextmenu.addItem('-');
	map.contextmenu.addItem({text: 'Show all items', iconCls: 'osm-link-ext', callback: goShowAll});
	map.contextmenu.addItem({text: 'Link to this map', iconCls: 'osm-link', callback: linkToThisMap});
	map.contextmenu.addItem({text: 'Find my position', iconCls: 'osm-direction', callback: findMyLocation});
	map.contextmenu.addItem({separator: true});
	map.contextmenu.addItem({text: 'Zoom in', iconCls: 'osm-zoom-in', callback: zoomIn});
	map.contextmenu.addItem({text: 'Zoom out', iconCls: 'osm-zoom-out', callback: zoomOut});
	/* END leaflet-locatecontrol */

	/* BEGIN piwigo-openstreetmap plugin */
	map.on('moveend', onMapMove);

	function onMapMove(e){
		//getMarkers();
	}

	/* Generate the carrousel */
	function getMarkers(){
		/* Get coordonates */
		var bounds = map.getBounds();
		var min = bounds.getSouthWest().wrap();
		var max = bounds.getNorthEast().wrap();

		/* Remove any previous thumbnails */
		removeChildren({parentId:'jcarousel-thumb'});

		var nb_items = 0;
		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];
			var pathurl = a[3];

			//console.log(a[0] +" > "+ min.lat +" && "+ a[1] +" > "+ min.lng +" && "+ a[0] +" < "+ max.lat +" && "+ a[1] +" < "+ max.lng);
			if (a[0] > min.lat && a[1] > min.lng && a[0] < max.lat && a[1] < max.lng)
			{
				//console.log("Inside bounds");
				var li_elem = document.createElement("li");
				var img_elem = document.createElement("img");
				img_elem.setAttribute("id","jcarousel-img");
				img_elem.setAttribute("width","50");
				img_elem.setAttribute("height","50");
				img_elem.setAttribute("onclick","OnThumbClick("+i+")");
				img_elem.setAttribute("class","morph pic");
				img_elem.src = pathurl;
				document.getElementById("jcarousel-thumb").appendChild(li_elem).appendChild(img_elem);
				nb_items++;
				//console.log("Inside bounds:"+i);
			}
		}
		/* Update items counts */
		document.getElementById("nb_showall").innerHTML = nb_items +' items';
	}

	function OnThumbClick(Id){
		/* http://leaflet.github.io/Leaflet.markercluster/example/marker-clustering-zoomtoshowlayer.html */
		//console.log("Open:"+Id+" "+MarkerClusterList[Id].getLatLng());
		markers.zoomToShowLayer(MarkerClusterList[Id], function () {
				map.panTo(MarkerClusterList[Id].getLatLng());
				MarkerClusterList[Id].togglePopup();
		});
	}

	function removeChildren (params){
		var parentId = params.parentId;

		var childNodes = document.getElementById(parentId).childNodes;
		for(var i=childNodes.length-1;i >= 0;i--){
			var childNode = childNodes[i];
			childNode.parentNode.removeChild(childNode);
		}
	}

	/* BEGIN leaflet Location */
	function onLocationFound(e) {
		var radius = e.accuracy / 2;

		L.marker(e.latlng).addTo(map)
			.bindPopup("You are within " + radius + " meters from this point").openPopup();

		L.circle(e.latlng, radius).addTo(map);
	}

	function onLocationError(e) {
		alert(e.message);
	}

	map.on('locationfound', onLocationFound);
	map.on('locationerror', onLocationError);

{/literal}
</script>
<!--
<link rel="stylesheet" href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css">
<link rel="stylesheet" href="{$OSM_PATH}leaflet/jcarousel.responsive.css" />
-->
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<!--
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<script src="{$OSM_PATH}leaflet/jquery.jcarousel.min.js"></script>
-->
<script type="text/javascript" src="{$OSM_PATH}leaflet/jquery.tinyscrollbar.js"></script>
<script>
{literal}

	/* Init Jquery */
	(function($) {

		$("#scrollbar1").tinyscrollbar({ axis: "y"});
	
		$('#dialog').dialog({autoOpen: false, minHeight: 150, minWidth: 600});

		$('#opener').click(function() {
			$('#dialog').dialog('open');
		});

	})(jQuery);
{/literal}
</script>

</body>
</html>
