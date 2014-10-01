<?php
// Begin CORS handling ---------------------------------------------------------
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Max-Age: 86400'); // Cache preflight request

// Exit early so that the page isn't fully loaded for OPTIONS requests
if ( strtolower($_SERVER['REQUEST_METHOD']) == 'options' ) exit;

// If raw post data, this could be from IE8 XDomainRequest
//if (isset($_POST) && !isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = file_get_contents('php://input');
// Only use this if you want to populate $_POST in all instances
if ( isset($HTTP_RAW_POST_DATA) ) {
  $data = explode('&', $HTTP_RAW_POST_DATA);
  foreach ($data as $val) {
    if ( !empty($val) ) {
      list($key, $value) = explode('=', $val);   
        $_POST[$key] = urldecode($value);
    }
  }
}

if ( get_magic_quotes_gpc() ) {
  function stripslashes_deep($value) {
    $value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
    return $value;
  }
  $_GET  = stripslashes_deep($_GET);
  $_POST = stripslashes_deep($_POST);
}
// End CORS handling -----------------------------------------------------------

$config_file = dirname(__FILE__) . '/settings.json';
// TODO: Cache this to avoid unnecessary I/O ops.
if ( is_file($config_file) ) {
  $conf = json_decode( file_get_contents($config_file), true );
} else {
  die("Error: settings.json file not found.");
}

$nid  = $_POST['nid'];
$nih  = $_POST['nih'];

$user_dir = dirname($config_file) . '/' . $conf['nimrod_dir'] . '/' . $nid . '/' . $nih;
$el_file  = $user_dir . '/eldata.json';
$ev_file  = $user_dir . '/evdata.json';

$info = json_decode($_POST['info'], true);

if ( isset($info['elements']) ) {

#  $resource_ids = array();
#  $els_visible  = array();
#  $els_size     = array();
#  foreach ($info['elements'] as $xpath => $val) {
#    $visibility = $val['visible'];
#    $size = $val['width'] * $val['height'];
#    foreach ($val['resources'] as $id) {
#      $els_visible[$id][] = $visibility;
#      $els_size[$id][]    = $size;
#      $resource_ids[]     = $id;
#    }
#  }
#  // Naive fusion: use the maximum value to assess importance for each element
#  $upds = array();
#  foreach ($resource_ids as $id) {
#    $upds[$id]['visible'] = max($els_visible[$id]);
#    $upds[$id]['size']    = max($els_size[$id]);
#  }

  if (is_file($ev_file)) unlink($ev_file);
  
  echo file_put_contents( $el_file, json_encode($info['elements']) );
  
}

if ( isset($info['events']) ) {

#  foreach ($info['events'] as $xpath => $val) {
#    foreach ($val['resources'] as $id) {
#      $resource_ids[] = $id;
#      $event[$id] = array();
#    }
#    foreach ($val['timeline'] as $timestamp => $event_name) {
#      $event[$id][$timestamp] = $event_name;
#    }
#  }

  if ( is_file($ev_file) ) {
    // Update timeline only
    $data = json_decode( file_get_contents( $ev_file ), true );
    foreach ($data as $xpath => $val) {
      foreach ($val['timeline'] as $timestamp => $event_name) {
        $info['events'][$xpath]['timeline'][$timestamp] = $event_name;
      }
    }
  }
  
  echo file_put_contents( $ev_file, json_encode($info['events']) );
  
}

