{html_head}
<link rel="stylesheet" href="{$OSM_PATH}fontello/css/osm.css" />
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

<div class="osm_layout">
    <form method="post" enctype="multipart/form-data">
        <p>
            <label>
                    <span class="property">{'File to upload:'|@translate}</span>
            </label>
            <input name="file_uploaded" type="file" value=""{if isset($uploaded_errors.file)} class="file_uploader_error"{/if} />
            {foreach from=$uploaded_errors.file item=error_description}<p class="gps_error_description">{$error_description}</ip>{/foreach}
        </p>
        <p>
            <label>
                <span class="property">{'Album:'|@translate}</span>
            </label>
            <select style="width:400px" name="category" size="1">
                {html_options options=$category_gps}
            </select>
        </p>
        <p>
            <input class="submit" name="submit" type="submit" value="{'Submit'|@translate}" />
        </p>
    </form>
</div>
