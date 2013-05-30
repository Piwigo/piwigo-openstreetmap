<?php
/***********************************************
* File      :   maintain.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Install / Uninstall method
*
* Created   :   28.05.2013
*
* Copyright 2013 <xbgmsharp@gmail.com>
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

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');
define('OSM_PATH' , PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');

function plugin_install()
{
	/* Modify images table */
	$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD COLUMN `lat` DOUBLE(10,8) COMMENT "latitude used by the piwigo-openstreetmap plugin"';
	pwg_query($q);
	$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD INDEX images_lat(`lat`)';
	pwg_query($q);
	$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD COLUMN `lon` DOUBLE(11,8) COMMENT "longitude used by the piwigo-openstreetmap plugin"';
	pwg_query($q);

	/* Add configuration to the config table */
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_add_before", "Average", "Where to display the map used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_height", "200", "Map height in px used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_zoom", "12", "Zoomlevel used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_baselayer", "mapnik", "Map style used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_custombaselayer", "", "Custom map style used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_custombaselayerurl", "", "Tile server URL used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_noworldwarp", "0", "No Worldwarp used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_attrleaflet", "1", "Show \'Powered by Leaflet\' used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_attrimagery", "1", "Show map style used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_attrmodule", "1", "Show Author note used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );
	$q = 'INSERT INTO '.CONFIG_TABLE.' (param,value,comment)
		VALUES ("osm_auto_sync", "1", "Auto sync geotag on upload used by the piwigo-openstreetmap plugin");';
	pwg_query( $q );

	// Create world map link
	$dir_name = basename( dirname(__FILE__) );
	$c = <<<EOF
<?php
define('PHPWG_ROOT_PATH','./');
include_once( PHPWG_ROOT_PATH. 'plugins/$dir_name/osmmap.php');
?>
EOF;
	$fp = fopen( PHPWG_ROOT_PATH.'osmmap.php', 'w' );
	fwrite( $fp, $c);
	fclose( $fp );
}

function plugin_uninstall()
{
	// Delete all files
	if (is_dir(PHPWG_ROOT_PATH.PWG_LOCAL_DIR.'piwigo-openstreetmap'))
	{
		deltree(PHPWG_ROOT_PATH.PWG_LOCAL_DIR.'piwigo-openstreetmap');
	}

	// Remove world map link
	@unlink(PHPWG_ROOT_PATH.'osmmap.php');

	/* Remove configuration from the config table */
	$q = 'DELETE FROM '.CONFIG_TABLE.' WHERE param LIKE "%osm_%" LIMIT 10;';
	pwg_query( $q );

	/* Remove geotag from images table */
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lat`';
	pwg_query( $q );
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lon`';
	pwg_query( $q );
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP INDEX `images_lat`';
	pwg_query( $q );
}

function plugin_activate()
{
	global $conf;

	if ( (!isset($conf['osm_height'])) or (!isset($conf['osm_zoom']))
	or (!isset($conf['osm_baselayer'])) or (!isset($conf['osm_custombaselayer']))
	or (!isset($conf['osm_custombaselayerurl'])) or (!isset($conf['osm_noworldwarp']))
	or (!isset($conf['osm_attrleaflet'])) or (!isset($conf['osm_attrimagery']))
	or (!isset($conf['osm_attrmodule']))  or (!isset($conf['osm_auto_sync']))
	or (!isset($conf['osm_add_before'])) )
	{
		plugin_install();
	}
}

function deltree($path)
{
	if (is_dir($path))
	{
		$fh = opendir($path);
		while ($file = readdir($fh))
		{
			if ($file != '.' and $file != '..')
			{
				$pathfile = $path . '/' . $file;
				if (is_dir($pathfile))
				{
					deltree($pathfile);
				}
				else
				{
					@unlink($pathfile);
				}
			}
		}
		closedir($fh);
		return @rmdir($path);
	}
}


?>
