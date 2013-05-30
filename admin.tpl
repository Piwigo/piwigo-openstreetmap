<div class="titrePage">
  <h2>OpenStreetMap plugin</h2>
</div>

You have {$NB_GEOTAGGED} geotagged images.

<form method="post" action="" class="properties">
	<fieldset>
		<legend>{'R_MAP'|@translate}</legend>
		<ul>
			<li>
				<label>{'ADD_BEFORE'|@translate} : </label>
				<select name="osm_add_before">
					{html_options options=$AVAILABLE_ADD_BEFORE selected=$SELECTED_ADD_BEFORE}
				</select>
				<br/><small>{'ADD_BEFORE_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'HEIGHT'|@translate} : </label>
				<input type="text" value="{$HEIGHT}" name="osm_height" size="4"/>
				<br/><small>{'HEIGHT_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ZOOM'|@translate} : </label>
				<select name="osm_zoom">
					{html_options options=$AVAILABLE_ZOOM selected=$SELECTED_ZOOM}
				</select>
				<br/><small>{'ZOOM_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<legend>{'G_MAP'|@translate}</legend>
		<ul>
			<li>
				<label>{'BASELAYER'|@translate} : </label>
				<select name="osm_baselayer">
					{html_options options=$AVAILABLE_BASELAYER selected=$SELECTED_BASELAYER}
				</select>
				<br/><small>{'BASELAYER_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'CUSTOMBASELAYER'|@translate} : </label>
				<input type="text" value="{$CUSTOMBASELAYER}" name="osm_custombaselayer" size="40"/>
				<br/><small>{'CUSTOMBASELAYER_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'CUSTOMBASELAYERURL'|@translate} : </label>
				<input type="text" value="{$CUSTOMBASELAYERURL}" name="osm_custombaselayerurl" size="40"/>
				<br/><small>{'CUSTOMBASELAYERURL_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'NOWORLDWARP'|@translate} : </label>
				{html_radios name='osm_noworldwarp' values='true,false'|@explode output='Yes,No'|@explode|translate selected=$NOWORLDWARP}
				<br/><small>{'NOWORLDWARP_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRLEAFLET'|@translate} : </label>
				{html_radios name='osm_attrleaflet' values='true,false'|@explode output='Yes,No'|@explode|translate selected=$ATTRLEAFLET}
				<br/><small>{'ATTRLEAFLET_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRIMAGERY'|@translate} : </label>
				{html_radios name='osm_attrimagery' values='true,false'|@explode output='Yes,No'|@explode|translate selected=$ATTRIMAGERY}
				<br/><small>{'ATTRIMAGERY_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRMODULE'|@translate} : </label>
				{html_radios name='osm_attrmodule' values='true,false'|@explode output='Yes,No'|@explode|translate selected=$ATTRMODULE}
				<br/><small>{'ATTRMODULE_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<legend>{'PLUGINCONF'|@translate}</legend>
		<ul>
			<li>
				<label>{'AUTOSYNC'|@translate} : </label>
				{html_radios name='osm_auto_sync' values='true,false'|@explode output='Yes,No'|@explode|translate selected=$AUTO_SYNC}
				<br/><small>{'AUTOSYNC_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>
	<p>
		<input class="submit" type="submit" value="{'Save Settings'|@translate}" name="submit"/>
	</p>
</form>
