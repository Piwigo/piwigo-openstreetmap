{html_head}
<link rel="stylesheet" href="{$OSM_PATH}fontello/css/osm.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/leaflet.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/leaflet-search.min.css" />
<link rel="stylesheet" href="{$OSM_PATH}leaflet/Leaflet.EditInOSM.css" />
<script src="{$OSM_PATH}leaflet/leaflet.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet-search.min.js"></script>
<script src="{$OSM_PATH}leaflet/leaflet-providers.js"></script>
<script src="{$OSM_PATH}leaflet/Leaflet.EditInOSM.js"></script>
{/html_head}

{html_style}
{literal}
#map { height: 400px; width: 100%; }
{/literal}
{/html_style}

<h2>{$TITLE} &#8250; {'Edit photo'|@translate} {$TABSHEET_TITLE}</h2>

{if not empty($errors)}
  <h3>{'SYNC_ERRORS'|@translate}</h3>
  <div class="errors">
    <ul>
      {foreach from=$errors item=error}
      <li>{$error}</li>
      {/foreach}
    </ul>
  </div>
{/if}

<form action="{$F_ACTION}" method="post" id="openstreetmap">

	<input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
	<fieldset>
		<legend>{'Properties'|@translate}</legend>
		<div style="float: left;">
			<img src="{$TN_SRC}" alt="{'Thumbnail'|@translate}" style="border: 2px solid rgb(221, 221, 221);">
		</div>
		<div style="float: left; margin: auto;">
			<ul>
				<li>
					<label><input type="text" size="9" name="osmlat" value="{$LAT}"> {'LATITUDE'|@translate} (-90=S to 90=N)</label>
				</li>
				<li>
					<label><input type="text" size="9" name="osmlon" value="{$LON}"> {'LONGITUDE'|@translate} (-180=W to 180=E)</label>
				</li>
			</ul>
		</div>
	</fieldset>

	<fieldset>
		<legend>{'EDIT_MAP'|@translate}</legend>
		{'EDIT_UPDATE_LOCATION_DESC'|@translate}
		<div id="map"></div>
		<div id="info">
			<small>Search values: OpenStreetMap Data offer by MapQuest Open Platform 
			<a href="http://open.mapquestapi.com/nominatim/">open.mapquestapi.com</a></small>
		</div>
	</fieldset>

	<p>
		<input class="submit" type="submit" value="{'Save Settings'|@translate}" name="submit"/>
	</p>
</form>

{literal}
<script type="text/javascript">

	{/literal}
	{$OSM_JS}
	{literal}

	var popup = L.popup();

	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(map);
		var form=document.forms["openstreetmap"]
		form.osmlat.value = Math.ceil(e.latlng.lat * 100000) / 100000;
		form.osmlon.value = Math.ceil(e.latlng.lng * 100000) / 100000;
	}

	map.on('click', onMapClick);

	/* BEGIN leaflet-search */
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
			animateLocation: false,
			markerLocation: true,
			zoom: 10,
			minLength: 3,
			autoType: false,
			position: 'topright'
		};

	map.addControl( new L.Control.Search(searchOpts) );
	/* END leaflet-search */
</script>
{/literal}