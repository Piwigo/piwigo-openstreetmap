{combine_script id='qleaflet' load='header' path="{$OSM_PATH}/leaflet/qleaflet.jquery.js"}

<div class="full-line-box">
  <strong>{'OSM Geotag'|@translate}</strong>
</div>

<div class="half-line-info-box">
  <label>{'Latitude'|@translate}</label>
  <input type="text" size="8" id="osmlat-{$element.ID}" class="latitude" name="osmlat-{$element.ID}" value="{$element.latitude}">
</div>
<div class="half-line-info-box">
  <label>{'Longitude'|@translate}</label>
  <input type="text" size="9" id="osmlon-{$element.ID}" class="longitude" name="osmlon-{$element.ID}" value="{$element.longitude}">
</div>

<div class="full-line-box">
  <div class="osm-map-{$element.ID} map1" data-markerpos="{$element.latitude},{$element.longitude}" data-markertext="{$element.name}" data-formid="{$element.id}"></div>
</div>


{html_style}
.map1 { 
  height: {$batch_unit_height}px !important;
  width:100% !important; margin: 5px; 
} 
{/html_style}

<script>
{literal}
$(document).ready(function () {

pluginValues.push({ api_key: "latitude", selector: ".latitude" });
pluginValues.push({ api_key: "longitude", selector: ".longitude" });

  $('fieldset.elementEdit').each(function () {
    const fieldset = $(this);
    const imageID = fieldset.data('image_id');
    const latSelector = `#osmlat-${imageID}`;
    const lonSelector = `#osmlon-${imageID}`;
    const mapSelector = `.osm-map-${imageID}`;

    // Init the map
    $(mapSelector).qleaflet();

    // Get exisitng values 
    let prevLat = $(latSelector).val();
    let prevLon = $(lonSelector).val();

    setInterval(() => {
      const currentLat = $(latSelector).val();
      const currentLon = $(lonSelector).val();

      // Check if values have changed
      if (currentLat !== prevLat || currentLon !== prevLon) {
        // Update previous values
        prevLat = currentLat;
        prevLon = currentLon;

        // If both are filled, update pluginValues and trigger badge
        if (currentLat && currentLon) {
          // Remove previous pluginValues entries
          pluginValues = pluginValues.filter(entry =>
            !(entry.selector === latSelector || entry.selector === lonSelector)
          );

          // Add values to batch unit 

          // Show unsaved badge
          showUnsavedLocalBadge(imageID);
        }
      }
    }, 300);
  });
});
{/literal}
</script>

