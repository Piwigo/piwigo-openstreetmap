<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function osm_ws_add_methods($arr)
{
  $service = &$arr[0];

  //osm.setInfo method registration
  $service->addMethod(
    'osm.setInfo',
    'osm_ws_setInfo',
    array(
      'image_id' => array(
        'type' => WS_TYPE_INT | WS_TYPE_POSITIVE | WS_TYPE_NOTNULL,
      ),
      'latitude' => array(
        'type' => WS_TYPE_FLOAT | WS_TYPE_NOTNULL,
      ),
      'longitude' => array(
        'type' => WS_TYPE_FLOAT | WS_TYPE_NOTNULL,
      ),
    ),
    'Update the osm fields, latitude and longitude in the piwigo_images table',
    null,
    array(
      'hidden' => false,
      'admin_only' => true,
      'post_only' => true,
    )
  );
}

// Hook to perform the action on in single mode
function osm_ws_setInfo()
{
  if (!isset($params['image_id']) || !isset($params['latitude']) || !isset($params['latitude'])) {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid latitude or longitude value for file');
  }

  $data = array(
		'id' => intval($params['image_id']),
		'latitude' => trim($params['latitude']),
		'longitude' => trim($params['longitude'])
	);

  if ( strlen($data['latitude'])>0 and strlen($data['longitude'])>0 )
  {
    if ( !is_numeric($data['latitude']) or !is_numeric($data['longitude'])
      or (double)$data['latitude']>90 or (double)$data['latitude']<-90
      or (double)$data['longitude']>180 or (double)$data['longitude']<-180 )
      return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid latitude or longitude value for file');
  }
  elseif ( strlen($data['latitude'])==0 and strlen($data['longitude'])==0 )
  {
    // nothing
  }
  else
  {
    return new PwgError(WS_ERR_INVALID_PARAM, 'Invalid latitude or longitude value for file');
  }

  mass_updates(
		IMAGES_TABLE,
		array(
			'primary' => array('id'),
			'update' => array('latitude', 'longitude')
		),
		$datas
	);

  return array(
    'status' => 'success',
    'message' => 'OSM fields updated successfully for picture ' . $image_id,
  );
}

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
