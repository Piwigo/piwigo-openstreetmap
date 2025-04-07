<div class="map_container"> 
  <div>
    <label>{'Latitude'|translate}(-90=S to 90=N)
      <input type="text" size="8" id="osmlat" name="osmlat">
    </label>
    <label>{'Longitude'|translate} (-180=W to 180=E)
      <input type="text" size="9" id="osmlon" name="osmlon">
    </label>
    <p>{'EMPTY_COORD_VAL_WARNING'|translate}</p>
    <label>Saved Places:</label>
    
    <select id="osmplaces" name="osmplaces" >
        <option value="NULL">--</option>
        {implode("\n",$place_options)}
    </select>
  </div>
  <div>
    <div class="osm-map1 map1"></div>
  </div>
</div>

{html_style}
.map1 { 
  height: {$batch_global_height}px !important;
  width:100% !important;
  margin: 5px; 
 } 
{/html_style}

{combine_script id='qleaflet' load='footer' path='plugins/piwigo-openstreetmap/leaflet/qleaflet.jquery.js'}


{footer_script require='qleaflet'}
{$jsplaces}


$(document).ready(function() {
  var map;
      $("#permitAction").on("change", function (e) {
          var optionSelected = $("option:selected", this);
          if ("openstreetmap" == optionSelected.val()) {
            map = $(".osm-map1").qleaflet();
      map.click(function(a,b,c) {
      $("#osmplaces").val("NULL");
      });
          }
      });
  $("#osmplaces").change(function(){
  var select = $("#osmplaces").val();
  var lat_elem = $("#osmlat");
  var lon_elem = $("#osmlon");
  if (select == "NULL")
  {
    lat_elem.val(0);
    lon_elem.val(0);
  }
    else
  {
    lat_elem.val(arr_places[select][1]);
    lon_elem.val(arr_places[select][2]);
  }
  });
});


{/footer_script}