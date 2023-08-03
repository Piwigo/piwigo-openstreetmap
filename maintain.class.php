<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

/**
 * This class is used to expose maintenance methods to the plugins manager
 * It must extends PluginMaintain and be named "PLUGINID_maintain"
 * where PLUGINID is the directory name of your plugin.
 */
class piwigo_openstreetmap_maintain extends PluginMaintain
{
  private $default_conf = array(
    'right_panel' => array(
      'enabled' => true,
      'add_before' => 'Average',
      'height' => '200',
      'zoom' => 12,
      'link' => 'Location',
      'linkcss' => null,
      'showosm' => true,
      ),
    'left_menu' => array(
      'enabled' => true,
      'link' => 'OSWORLDMAP',
      'popup' => 0,
      'popupinfo_name' => true,
      'popupinfo_img' => true,
      'popupinfo_link' => true,
      'popupinfo_comment' => true,
      'popupinfo_author' => true,
      'zoom' => 2,
      'center' => '0,0',
      'layout' => 2,
      ),
    'category_description' => array(
      'enabled' => true,
      'index' => 0,
      'height' => '200',
      'width' => 'auto',
      ),
    'main_menu' => array(
      'enabled' => false,
      'height' => '200',
      ),
    'gpx' => array(
      'height' => '500',
      'width' => '320',
      ),
    'batch' => array(
      'global_height' => '200',
      'unit_height' => '200',
      ),
    'map' => array(
      'baselayer' => 'mapnik',
      'custombaselayer' => null,
      'custombaselayerurl' => null,
      'noworldwarp' => false,
      'attrleaflet' => true,
      'attrimagery' => true,
      'attrplugin' => true,
      ),
    'pin' => array(
      'pin' => 1,
      'pinpath' => '',
      'pinsize' => '',
      'pinshadowpath' => '',
      'pinshadowsize' => '',
      'pinoffset' => '',
      'pinpopupoffset' => '',
      ),
  );

  private $table;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id); // always call parent constructor

    global $prefixeTable;

    // Class members can't be declared with computed values so initialization is done here
    $this->table = $prefixeTable . 'osm_places';
  }

  /**
   * Plugin installation
   *
   * Perform here all needed step for the plugin installation such as create default config,
   * add database tables, add fields to existing tables, create local folders...
   */
  function install($plugin_version, &$errors=array())
  {
    global $conf;

    // add config parameter
    if (empty($conf['osm_conf']))
    {
      conf_update_param('osm_conf', $this->default_conf, true);
    }
    else
    {
      $old_conf = safe_unserialize($conf['osm_conf']);

      foreach ($this->default_conf as $conf_settings_group_key => $conf_settings_group_value)
      {
        if (!isset($old_conf[$conf_settings_group_key]))
        {
          $old_conf[$conf_settings_group_key] = $conf_settings_group_value;
        }
        else
        {
          foreach ($conf_settings_group_value as $key => $value)
          {
            if (!isset($old_conf[$conf_settings_group_key][$key]))
            {
              $old_conf[$conf_settings_group_key][$key] = $value;
            }
          }
        }
      }

      conf_update_param('osm_conf', $old_conf, true);
    }

    // add a new table
    pwg_query('
CREATE TABLE IF NOT EXISTS `'.$this->table.'` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `latitude` double(8,6) NOT NULL,
  `longitude` double(9,6) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `parentId` mediumint(8),
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
;');

    // change "longitude" type, created as double(8,6) in a previous version of the plugin
    $columns = query2array('SHOW COLUMNS FROM `'.$this->table.'` LIKE "longitude";');
    if ('double(9,6)' != $columns[0]['Type'])
    {
      pwg_query('ALTER TABLE `'.$this->table.'` CHANGE `longitude` `longitude` double(9,6) NOT NULL;');
    }

    if (conf_get_param('osm_add_osmmap.php', true))
    {
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
      if (!file_put_contents(PHPWG_ROOT_PATH.'osmmap.php', $c)) {
        error_reporting($_error_reporting);
        throw new SmartyException("unable to write file {$PHPWG_ROOT_PATH.'osmmap.php'}");
      }      
    }
  }

  /**
   * Plugin activation
   *
   * This function is triggered after installation, by manual activation or after a plugin update
   * for this last case you must manage updates tasks of your plugin in this function
   */
  function activate($plugin_version, &$errors=array())
  {
  }

  /**
   * Plugin deactivation
   *
   * Triggered before uninstallation or by manual deactivation
   */
  function deactivate()
  {
  }

  /**
   * Plugin (auto)update
   *
   * This function is called when Piwigo detects that the registered version of
   * the plugin is older than the version exposed in main.inc.php
   * Thus it's called after a plugin update from admin panel or a manual update by FTP
   */
  function update($old_version, $new_version, &$errors=array())
  {
    // I (mistic100) chosed to handle install and update in the same method
    // you are free to do otherwize
    $this->install($new_version, $errors);
  }

  /**
   * Plugin uninstallation
   *
   * Perform here all cleaning tasks when the plugin is removed
   * you should revert all changes made in 'install'
   */
  function uninstall()
  {
    // delete configuration
    conf_delete_param('osm_conf');

    // delete table
    pwg_query('DROP TABLE `'. $this->table .'`;');

    if (conf_get_param('osm_remove_osmmap.php', true))
    {
      @unlink(PHPWG_ROOT_PATH.'osmmap.php');
    }
  }
}