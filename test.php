<?php 
require 'phplibs/class.nimrod.poutil.php';
// Declare (and use) _gt and _gx functions for gettext and gettext+context.
require 'phplibs/class.nimrodgettext.php';
// Enable logging.
define( 'GETTEXT_LOG_UNTRANSLATED', dirname( __FILE__ ) . '/gettextlogused' );
// Optionally point to the CMS installation dir.
NimrodLogger::$abspath = '/var/www/prhlt/wordpress';

function nimrod_parse( $buffer ) {
  NimrodLogger::resetFiles();
  $np = new NimrodParser;
  $np->loadString($buffer)->parse();
  NimrodLogger::addResourceComments($np->resources);
  NimrodLogger::save();
  return $np;
}

ob_start( 'nimrod_parse' );

// Sample page starts ---------------------------------------------------------
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv='Content-type' content='text/html; charset=UTF-8' />
<title><?=_gt('Hi there')?></title>
<style type="text/css">
p#k { font-size:2em; }
</style>
</head>
<body>

<p id="k" alt="<?=sprintf( _gt('You have %d items'), 544 )?>">
  <img alt="<?=_gt('You have 544 items')?>" /><br>
  <a title="<?=sprintf( _gt('Noun %s'), _gt('expando') )?>" alt="<?=_gt('Hi')?>" href="#"><?=_gt('You have 544 items')?></a>
</p>

<span><?=sprintf( _gt('%s at %s'), date("Y/m/d"), date("H:i") )?></span>
<strong><?=_gt('Hi again')?></strong>
<strong><?=_gx('Hi again', 'testing')?></strong>
<strong><?=_gt('Hi again')?></strong>
<strong><?=_gt('Hi again')?></strong>
<p><strong><?=_gt('Hi again')?></strong></p>

</body>
</html>
<?php
// Sample page ends -----------------------------------------------------------

ob_end_flush();
?>
