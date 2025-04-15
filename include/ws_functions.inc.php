<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

// this function hooks to pwg.images.setInfo calls which contains OSM data
// which allows to process additional data in a single HTTP request
function osm_ws_images_setInfo($res, $methodName, $params) {
  //Make sure the api method called is the set info
  if ($methodName != 'pwg.images.setInfo') {
    return $res;
  }

  if (!isset($params['image_id']))
  {
    return $res;
  }

  if (empty($params['latitude'])) {
    return $res;
  }

  if (empty($params['longitude'])) {
    return $res;
  }
  
  $image_id = $params['image_id'];
  $update = array(
    array(
      'id' => $image_id,
      'latitude' => $_POST['latitude'],
      'longitude' => $_POST['longitude'],
      
    )
  );

  mass_updates(
    IMAGES_TABLE,
    array(
      'primary' => array('id'),
      'update'  => array('latitude','longitude')
    ),
    $update
  );
  return $res;
}
