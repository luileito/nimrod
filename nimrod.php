<?php
/*
    Plugin Name: Nimrod
    Plugin URI: https://github.com/luileito/nimrod/
    Description: Prioritize localization resources.
    Version: 1.0
    Author: Luis A. Leiva
    Author URI: http://personales.upv.es/luileito/
    License: dual MIT + GPL3
*/

$n_dir = get_option('nimrod_dir');
if ( $n_dir !== FALSE ) {
  // This is for compatibility with other implementations of Nimrod.
  define( 'GETTEXT_LOG_UNTRANSLATED', dirname( __FILE__ ) . '/' . $n_dir );
}

// The plugin is pretty abstracted to begin with.
require 'phplibs/class.nimrodwordpress.php';
$nimrod = new WP_Nimrod();

