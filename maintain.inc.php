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

function plugin_install()
{
	/* Modify images table if require */
	$q = 'SELECT COUNT(*) as nb FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = "'.IMAGES_TABLE.'" AND COLUMN_NAME = "lat" OR COLUMN_NAME = "lon"';
	$result = pwg_db_fetch_array( pwg_query($q) );
	if($result['nb'] != 2)
	{
		$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD COLUMN `lat` DOUBLE(10,8) COMMENT "latitude used by the piwigo-openstreetmap plugin"';
		pwg_query($q);
		$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD INDEX images_lat(`lat`)';
		pwg_query($q);
		$q = 'ALTER TABLE '.IMAGES_TABLE.' ADD COLUMN `lon` DOUBLE(11,8) COMMENT "longitude used by the piwigo-openstreetmap plugin"';
		pwg_query($q);
	}

	$default_config = array(
		'right_panel' => array(
			'enabled' 	=> true,
			'add_before' 	=> 'Average',
			'height' 	=> '200',
			'zoom' 		=> 12,
			'link'		=> 'Location',
			'linkcss'	=> null,
			'showosm' 	=> true,
			),
		'left_menu' => array(
			'enabled'		=> true,
			'link'			=> 'OS World Map',
			'popup'			=> 0,
			'popupinfo_name'	=> true,
			'popupinfo_img'		=> true,
			'popupinfo_link'	=> true,
			'popupinfo_comment'	=> true,
			'popupinfo_author'	=> true,
			),
		'map' => array(
			'baselayer' 		=> 'mapnik',
			'custombaselayer' 	=> null,
			'custombaselayerurl'	=> null,
			'noworldwarp' 		=> false,
			'attrleaflet' 		=> true,
			'attrimagery' 		=> true,
			'attrplugin' 		=> true,
			),
		'auto_sync' 		=> false,
		'batch_manager' 	=> false,
	);
	/* Add configuration to the config table */
	$conf['osm_conf'] = serialize($default_config);
	conf_update_param('osm_conf', $conf['osm_conf']);

	$q = 'UPDATE '.CONFIG_TABLE.' SET `comment` = "Configuration settings for piwigo-openstreetmap plugin" WHERE `param` = "osm_conf";';
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
	/* Delete all files */
/* Don't remove myself on restore settings
	if (is_dir(OSM_PATH))
	{
		deltree(OSM_PATH);
	}
*/
	// Remove world map link
	@unlink(PHPWG_ROOT_PATH.'osmmap.php');

	/* Remove configuration from the config table */
	$q = 'DELETE FROM '.CONFIG_TABLE.' WHERE param = "osm_conf" LIMIT 1;';
	pwg_query( $q );

	/* Remove geotag from images table */
/*
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lat`';
	pwg_query( $q );
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lon`';
	pwg_query( $q );
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP INDEX `images_lat`';
	pwg_query( $q );
*/
}

function plugin_activate()
{
	global $conf;

	if ( (!isset($conf['osm_conf'])) )
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
