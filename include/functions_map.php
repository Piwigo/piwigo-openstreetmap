<?php
/***********************************************
* File      :   functions_map.php
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


function osm_parse_map_data_url($tokens, &$next_token)
{
    $page = parse_section_url($tokens, $next_token);
    if ( !isset($page['section']) )
      $page['section'] = 'categories';
  
    $page = array_merge( $page, parse_well_known_params_url( $tokens, $next_token) );
    $page['start']=0;
    $page['box'] = osm_bounds_from_url( @$_GET['box'] );
    return $page;
}

function osm_bounds_from_url($str)
{
  if ( !isset($str) or strlen($str)==0 )
    return null;
  $r = explode(',', $str );
  if ( count($r) != 4)
    bad_request( $str.' is not a valid geographical bound' );
  $b = array(
    's' => $r[0],
    'w' => $r[1],
    'n' => $r[2],
    'e' => $r[3],
  );
  return $b;
}

?>