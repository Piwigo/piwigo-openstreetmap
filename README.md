piwigo-openstreetmap
==============

[OpenStreetMap](http://OpenStreetMap.org/) port for piwigo. Add a OpenSource Map on your web gallery!

Piwigo-openstreetmap is a [plugin](http://piwigo.org/ext/extension_view.php?eid=701) for the [Piwigo](http://piwigo.org/) web gallery that allows you to render the geotag within your piwigo install.

Installation
------------

Upload this in ``your-gallery/plugins/`` dir.

* download the archive from github

        wget -O piwigo-openstreetmap.zip https://github.com/xbgmsharp/piwigo-openstreetmap/archive/master.zip

        unzip piwigo-openstreetmap.zip

        mv piwigo-openstreetmap-master piwigo-openstreetmap

* or clone the project 

        git clone git://github.com/xbgmsharp/piwigo-openstreetmap.git

Then, go to the admin site, in the plugin section and activate it.

Usage
-----

Go to a photo with Geo tag sync and see a map appear on the photo page.

Please refer to the [wiki](https://github.com/xbgmsharp/piwigo-openstreetmap/wiki) for additional information.

Docker
-----

The plugin writes a dispatch file (`osmmap.php`) at the gallery root on
install and on every reactivation.

If your container image keeps the gallery root inside the image rather
than on a persistent volume, the file is lost on each image upgrade.
Deactivate and reactivate the plugin to restore it, or bind mount a
host file over its location:

        # docker-compose.yml
        volumes:
          - /host/path/osmmap.php:/path/to/gallery/osmmap.php

with `osmmap.php` on the host:

        <?php
        define('PHPWG_ROOT_PATH', './');
        require __DIR__.'/plugins/piwigo-openstreetmap/osmmap.php';

Support
-----

To get support, please create new [issue](https://github.com/xbgmsharp/piwigo-openstreetmap/issues)

Help me improve the plugin, rate my [plugin](http://piwigo.org/ext/extension_view.php?eid=701), and if possible please send a greeting message to me ;)

Credit
------

* [OpenStreetMap](http://www.openstreetmap.org/)
* [leafletjs](http://leafletjs.com)
* [Leaflet.markercluster](https://github.com/Leaflet/Leaflet.markercluster)

Licence
-------
The piwigo-openstreetmap plugin for Piwigo is free software:  you can redistribute it
and/or  modify  it under  the  terms  of the  GNU  General  Public License  as
published by the Free Software Foundation.

This program  is distributed in the hope  that it will be  useful, but WITHOUT
ANY WARRANTY; without even the  implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/gpl.html>.
