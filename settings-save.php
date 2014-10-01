<?php
// This page is included in settings.php when the form is submitted.
require_once 'phplibs/functions.php';

function n_setting_dir_change( $dir ) {
  // If the dir is relative, point to Nimrod plugin dir.
  if ( !str_startswith($dir, '/') ) {
    $dir = plugin_dir_path(__FILE__) . $dir;
  }
  if ( !file_exists($dir) ) {
    if ( !mkdir($dir) ) {
      $status = array( 'error', __( 'Cannot create directory', 'nimrod') );
    } else {
      $status = array( 'updated', __( 'Directory created sucessfully', 'nimrod') );
    }
  } else if ( !is_writable($dir) ) {
    $status = array( 'error', __( 'Directory is not writable', 'nimrod') );
  } else {
    $status = array( 'updated', __('Directory saved successfully', 'nimrod') );
  }
  return $status;
}

$config_file = dirname(__FILE__) . '/settings.json';

$opt_dir = 'nimrod_dir';
$dirname = $_POST[$opt_dir];

$type = NULL;
$msg  = NULL;

if ( empty($dirname) ) {
  
  delete_option($opt_dir);
  $type = 'updated';
  $msg  = __( 'Directory unset', 'nimrod' );
  
  if ( is_file($config_file) ) {
    unlink($config_file);
  }
  
} else {

  if ( get_option( $opt_dir ) === FALSE ) {
    add_option( $opt_dir, $dirname );
    list($type, $msg) = n_setting_dir_change($dirname);
  } else {
    update_option( $opt_dir, $dirname );
    list($type, $msg) = n_setting_dir_change($dirname);    
  }  
  
  if ( $type != 'error' ) {
    $conf = array( $opt_dir => $dirname );
  }
  
}

// This setting is a checkbox, so it will be NULL if not checked.
$opt_logo = 'nimrod_logo';
$showlogo = $_POST[$opt_logo];
if ( !$showlogo ) {
  $showlogo = "off";
}
update_option( $opt_logo, $showlogo );

if (isset($conf)) {
  $conf[$opt_logo] = $showlogo;
} else {
  $conf = array( $opt_logo => $showlogo );
}

file_put_contents( $config_file, json_encode($conf) );

// Add notification to the WP error stack.
add_settings_error( 'nimrod_settings_dir_status', esc_attr( 'settings_updated' ), $msg, $type );

// Display notification.
settings_errors( 'nimrod_settings_dir_status' );
?>
