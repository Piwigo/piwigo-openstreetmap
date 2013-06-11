{html_head}
<style>
  {literal}
    .osm_layout {
      text-align: left;
      border: 2px solid rgb(221, 221, 221);
      padding: 1em;
      margin: 1em;
    }
  {/literal}
</style>
{/html_head}

<div class="titrePage">
  <h2>OpenStreetMap plugin</h2>
</div>

This plugin extends Piwigo with geographical location for each items.
<br/><br/>
Please read the <a href="https://github.com/xbgmsharp/piwigo-openstreetmap/wiki" target="_blanck">plugin documentation</a> for additional information.

<div class="osm_layout">
  <legend>{'Statistics'|@translate}</legend>
  <ul>
    <li class="update_summary_new">{$NB_GEOTAGGED} geotagged items in your gallery</li>
  </ul>
</div>

<form method="post" action="" class="properties">
	<fieldset>
		<legend>{'R_MAP'|@translate}</legend>
		<ul>
			<li>
				<label>{'SHOWLOCATION'|@translate} : </label>
				<label><input type="radio" name="osm_right_panel" value="true" {if $right_panel.enabled}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_right_panel" value="false" {if not $right_panel.enabled}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'SHOWLOCATION_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ADD_BEFORE'|@translate} : </label>
				<select name="osm_add_before">
					{html_options options=$AVAILABLE_ADD_BEFORE selected=$right_panel.add_before}
				</select>
				<br/><small>{'ADD_BEFORE_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'HEIGHT'|@translate} : </label>
				<input type="text" value="{$right_panel.height}" name="osm_height" size="4" required/>
				<br/><small>{'HEIGHT_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ZOOM'|@translate} : </label>
				<select name="osm_zoom">
					{html_options options=$AVAILABLE_ZOOM selected=$right_panel.zoom}
				</select>
				<br/><small>{'ZOOM_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'RIGHTLINK'|@translate} : </label>
				<input type="text" value="{$right_panel.link}" name="osm_right_link" size="10"/>
				<br/><small>{'RIGHTLINK_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'SHOWOSM'|@translate} : </label>
				<label><input type="radio" name="osm_showosm" value="true" {if $right_panel.showosm}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_showosm" value="false" {if not $right_panel.showosm}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'SHOWOSM_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<legend>{'L_MAP'|@translate}</legend>
		<ul>
			<li>
				<label>{'SHOWWORLDMAPLEFT'|@translate} : </label>
				<label><input type="radio" name="osm_left_menu" value="true" {if $left_menu.enabled}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_left_menu" value="false" {if not $left_menu.enabled}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'SHOWWORLDMAPLEFT_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'LEFTLINK'|@translate} : </label>
				<input type="text" value="{$left_menu.link}" name="osm_left_link" size="10"/>
				<br/><small>{'LEFTLINK_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'LEFTPOPUP'|@translate} : </label>
				<select name="osm_left_popup">
					{html_options options=$AVAILABLE_POPUP selected=$left_menu.popup}
				</select>
				<br/><small>{'LEFTPOPUP_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'LEFTPOPUPINFO'|@translate} : </label><br/>
				<div style="padding-left: 25px">
				  <input type="checkbox" name="osm_left_popupinfo_name" value="true" {if $left_menu.popupinfo_name}checked="checked"{/if}/> {'POPUPNAME'|@translate}<br />
				  <input type="checkbox" name="osm_left_popupinfo_img" value="true" {if $left_menu.popupinfo_img}checked="checked"{/if}/> {'POPUPTHUMB'|@translate}<br /> 
				  <input type="checkbox" name="osm_left_popupinfo_link" value="true" {if $left_menu.popupinfo_link}checked="checked"{/if}/> {'POPUPLINK'|@translate}<br /> 
				  <input type="checkbox" name="osm_left_popupinfo_comment" value="true" {if $left_menu.popupinfo_comment}checked="checked"{/if}/> {'POPUPCOMMENT'|@translate}<br /> 
				  <input type="checkbox" name="osm_left_popupinfo_author" value="true" {if $left_menu.popupinfo_author}checked="checked"{/if}/> {'POPUPAUTHOR'|@translate}
				</div>
				<small>{'LEFTPOPUPINFO_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>
	<fieldset>
		<legend>{'G_MAP'|@translate}</legend>
		<ul>
			<li>
				<label>{'BASELAYER'|@translate} : </label>
				<select name="osm_baselayer">
					{html_options options=$AVAILABLE_BASELAYER selected=$map.baselayer}
				</select>
				<br/><small>{'BASELAYER_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'CUSTOMBASELAYER'|@translate} : </label>
				<input type="text" value="{$map.custombaselayer}" name="osm_custombaselayer" size="40"/>
				<br/><small>{'CUSTOMBASELAYER_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'CUSTOMBASELAYERURL'|@translate} : </label>
				<input type="text" value="{$map.custombaselayerurl}" name="osm_custombaselayerurl" size="40"/>
				<br/><small>{'CUSTOMBASELAYERURL_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'NOWORLDWARP'|@translate} : </label>
				<label><input type="radio" name="osm_noworldwarp" value="true" {if $map.noworldwarp}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_noworldwarp" value="false" {if not $map.noworldwarp}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'NOWORLDWARP_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRLEAFLET'|@translate} : </label>
				<label><input type="radio" name="osm_attrleaflet" value="true" {if $map.attrleaflet}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_attrleaflet" value="false" {if not $map.attrleaflet}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'ATTRLEAFLET_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRIMAGERY'|@translate} : </label>
				<label><input type="radio" name="osm_attrimagery" value="true" {if $map.attrimagery}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_attrimagery" value="false" {if not $map.attrimagery}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'ATTRIMAGERY_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'ATTRPLUGIN'|@translate} : </label>
				<label><input type="radio" name="osm_attrplugin" value="true" {if $map.attrplugin}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_attrplugin" value="false" {if not $map.attrplugin}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'ATTRPLUGIN_DESC'|@translate}</small>
			</li>
		</ul>
<!--           <fieldset>
		  <legend>{'H_PIN'|@translate}</legend>
		  <ul>
			<li>
				<label>{'PIN'|@translate} : </label>
				<select name="osm_pin">
					{html_options options=$AVAILABLE_PIN selected=$SELECTED_PIN}
				</select>
				<br/><small>{'PIN_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINPATH'|@translate} : </label>
				<input type="text" value="{$PINPATH}" name="osm_pinpath" size="40"/>
				<br/><small>{'PINPATH_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINSIZE'|@translate} : </label>
				<input type="text" value="{$CUSTOMBASELAYERURL}" name="osm_pinsize" size="6/>
				<br/><small>{'PINSIZE_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINSHADOWPATH'|@translate} : </label>
				<input type="text" value="{$PINSHADOWPATH}" name="osm_pinshadowpath" size="40"/>
				<br/><small>{'PINSHADOWPATH_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINSHADOWSIZE'|@translate} : </label>
				<input type="text" value="{$PINSHADOWSIZE}" name="osm_pinshadowsize" size="4"/>
				<br/><small>{'PINSHADOWSIZE_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINOFFSET'|@translate} : </label>
				<input type="text" value="{$PINOFFSET}" name="osm_pinoffset" size="4"/>
				<br/><small>{'PINOFFSET_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'PINPOPUPOFFSET'|@translate} : </label>
				<input type="text" value="{$PINPOPUPOFFSET}" name="osm_pinpopupoffset" size="4"/>
				<br/><small>{'PINPOPUPOFFSET_DESC'|@translate}</small>
			</li>
		  </ul>
		</fieldset>
		-->
	</fieldset>
	<fieldset>
		<legend>{'PLUGINCONF'|@translate}</legend>
		<ul>
			<li>
				<label>{'AUTOSYNC'|@translate} : </label>
				<label><input type="radio" name="osm_auto_sync" value="true" {if $auto_sync}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_auto_sync" value="false" {if not $auto_sync}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'AUTOSYNC_DESC'|@translate}</small>
			</li>
			<li>
				<label>{'BATCHMANAGER'|@translate} : </label>
				<label><input type="radio" name="osm_batch_manager" value="true" {if $batch_manager}checked="checked"{/if}/> {'Yes'|@translate}</label>
				<label><input type="radio" name="osm_batch_manager" value="false" {if not $batch_manager}checked="checked"{/if}/> {'No'|@translate}</label>
				<br/><small>{'BATCHMANAGER_DESC'|@translate}</small>
			</li>
		</ul>
	</fieldset>

	<p>
		<input class="submit" type="submit" value="{'Save Settings'|@translate}" name="submit"/>
	</p>
</form>
