<?php
/***********************************************
* File      :   functions_metadata.php
* Project   :   piwigo-openstreetmap
* Descr     :   Read Geotag Metdata
* Base on   :   RV Maps & Earth plugin
*
* Created   :   30.05.2013
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

function osm_parse_fract( $f )
{
	$nd = explode( '/', $f );
	return $nd[1] ? ($nd[0]/$nd[1]) : 0;
}

function osm_parse_lat_lon( $arr )
{
	$v=0;
	$v += osm_parse_fract( $arr[0] );
	$v += osm_parse_fract( $arr[1] )/60;
	$v += osm_parse_fract( $arr[2] )/3600;
	return $v;
}

function osm_exif_to_lat_lon( $exif )
{
	$exif = array_intersect_key( $exif, array_flip( array('GPSLatitudeRef', 'GPSLatitude', 'GPSLongitudeRef', 'GPSLongitude') ) );
	if ( count($exif)!=4 )
		return '';
	if ( !in_array($exif['GPSLatitudeRef'], array('S', 'N') ) )
		return 'GPSLatitudeRef not S or N';
	if ( !in_array($exif['GPSLongitudeRef'], array('W', 'E') ) )
		return 'GPSLongitudeRef not W or E';
	if (!is_array($exif['GPSLatitude']) or !is_array($exif['GPSLongitude']) )
		return 'GPSLatitude and GPSLongitude are not arrays';

	$lat = osm_parse_lat_lon( $exif['GPSLatitude'] );
	if ( $exif['GPSLatitudeRef']=='S' )
		$lat = -$lat;
	$lon = osm_parse_lat_lon( $exif['GPSLongitude'] );
	if ( $exif['GPSLongitudeRef']=='W' )
		$lon = -$lon;
	return array ($lat,$lon);
}

?>
