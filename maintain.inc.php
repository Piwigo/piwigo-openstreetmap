<?php
/***********************************************
* File      :   maintain.inc.php
* Project   :   piwigo-openstreetmap
* Descr     :   Install / Uninstall method
*
* Created   :   28.05.2013
*
* Copyright 2013-2016 <xbgmsharp@gmail.com>
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
	global $prefixeTable;

	if (!defined('OSM_PATH'))
		define('OSM_PATH', PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)).'/');

	// Remove unused files from previous version
	$toremove = array("admin.tpl", "admin.php", "admin_boot.php",
	"leaflet/leaflet.ie.css", "leaflet/MarkerCluster.Default.ie.css",
	"admin/admin_sync.php", "admin/admin_sync.tpl", "admin/admin_gps.php", "admin/admin_gps.tpl");
	foreach ($toremove as $file)
	{
		if (is_file(OSM_PATH.$file))
		{
			@unlink(OSM_PATH.$file);
		}
	}

	$default_config = array(
		'right_panel' => array(
			'enabled'    => true,
			'add_before' => 'Average',
			'height'     => '200',
			'zoom'       => 12,
			'link'       => 'Location',
			'linkcss'    => null,
			'showosm'    => true,
			),
		'left_menu' => array(
			'enabled'           => true,
			'link'              => l10n('OSWORLDMAP'),
			'popup'             => 0,
			'popupinfo_name'    => true,
			'popupinfo_img'     => true,
			'popupinfo_link'    => true,
			'popupinfo_comment' => true,
			'popupinfo_author'  => true,
			'zoom'              => 2,
			'center'            => '0,0',
			'layout'            => 2,
			),
		'category_description' => array(
			'enabled'        => true,
			'index'          => 0,
			'height'         => '200',
			'width'          => 'auto',
			),
		'main_menu' => array(
			'enabled' => false,
			'height'  => '200',
			),
		'gpx' => array(
				'height' 	=> '500',
				'width' 	=> '320',
			),
		'batch' => array(
				'global_height' => '200',
				'unit_height' 	=> '200',
			),
		'map' => array(
			'baselayer'          => 'mapnik',
			'custombaselayer'    => null,
			'custombaselayerurl' => null,
			'noworldwarp'        => false,
			'attrleaflet'        => true,
			'attrimagery'        => true,
			'attrplugin'         => true,
			),
		'pin' => array(
			'pin'            => 1,
			'pinpath'        => '',
			'pinsize'        => '',
			'pinshadowpath'  => '',
			'pinshadowsize'  => '',
			'pinoffset'      => '',
			'pinpopupoffset' => '',
			),
	);
	/* Add configuration to the config table */
	$conf['osm_conf'] = serialize($default_config);
	conf_update_param('osm_conf', $conf['osm_conf']);

	$q = 'UPDATE '.CONFIG_TABLE.' SET `comment` = "Configuration settings for piwigo-openstreetmap plugin" WHERE `param` = "osm_conf";';
	pwg_query( $q );

	// Remove previous created db table for GPX entries
	$q = "DROP TABLE IF EXISTS ".$prefixeTable."osm_gps;";
	pwg_query( $q );

	// Remove previous created directory for GPX entries
	$gpx_dir = PHPWG_ROOT_PATH.PWG_LOCAL_DIR.'gps_track_files/';
	if (file_exists($gpx_dir) and is_dir($gpx_dir))
		osm_deltree($gpx_dir);

	// Easy access
	if (!defined('osm_place_table'))
		define('osm_place_table', $prefixeTable.'osm_places');

	/* Table to hold osm places details */
	$q = 'CREATE TABLE IF NOT EXISTS `'.osm_place_table.'` (
                `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
                `latitude` double(8,6) NOT NULL,
                `longitude` double(9,6) NOT NULL,
                `name` varchar(255) DEFAULT NULL,
                `parentId` mediumint(8),
                PRIMARY KEY (id)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8
        ;';
	pwg_query($q);

  // Increase size of longitude column from double(8,6) to double(9,6). GH#153.
  foreach(pwg_query('DESCRIBE '.osm_place_table) as $col_info)
  {
    $is_longitude = isset($col_info['Field'])
      && $col_info['Field'] === 'longitude';
    $is_old_size = isset($col_info['Type'])
      && $col_info['Type'] === 'double(8,6)';
    if ($is_longitude && $is_old_size)
    {
      $alter_longitude = 'ALTER TABLE `'.osm_place_table.'`'
        .' CHANGE `longitude` `longitude` double(9,6) NOT NULL';
      pwg_query($alter_longitude);
      // This is the only column to change, so we can leave the loop now.
      break;
    }
  }

	// Create world map link
	$dir_name = basename( dirname(__FILE__) );
	$c = <<<EOF
<?php
define('PHPWG_ROOT_PATH','./');
if (isset(\$_GET['v']) and \$_GET['v'] == 1)
	include_once( PHPWG_ROOT_PATH. 'plugins/piwigo-openstreetmap/osmmap.php');
else if (isset(\$_GET['v']) and \$_GET['v'] == 2)
	include_once( PHPWG_ROOT_PATH. 'plugins/piwigo-openstreetmap/osmmap2.php');
else if (isset(\$_GET['v']) and \$_GET['v'] == 3)
	include_once( PHPWG_ROOT_PATH. 'plugins/piwigo-openstreetmap/osmmap3.php');
else if (isset(\$_GET['v']) and \$_GET['v'] == 4)
	include_once( PHPWG_ROOT_PATH. 'plugins/piwigo-openstreetmap/osmmap4.php');
else
	include_once( PHPWG_ROOT_PATH. 'plugins/piwigo-openstreetmap/osmmap3.php');
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
		osm_deltree(OSM_PATH);
	}
*/
	// Remove world map link
	@unlink(PHPWG_ROOT_PATH.'osmmap.php');

	/* Remove configuration from the config table */
	$q = 'DELETE FROM '.CONFIG_TABLE.' WHERE param = "osm_conf" LIMIT 1;';
	pwg_query( $q );

	/* Remove lat/lon col from previous PWG install */
	//osm_drop_old_columns();
}

function plugin_activate()
{
	global $conf;

	if ( (!isset($conf['osm_conf']))
	    or (count($conf['osm_conf'], COUNT_RECURSIVE) != 43))
	{
		plugin_install();
	}
}

function osm_deltree($path)
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
					osm_deltree($pathfile);
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

// Drop lat/lon col from previous PWG install
function osm_drop_old_columns()
{
	/* TODO: delete columns if they exist in case of restore config */
	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lat`';
	pwg_query( $q );

	$q = 'ALTER TABLE '.IMAGES_TABLE.' DROP COLUMN `lon`';
	pwg_query( $q );
}

?>
