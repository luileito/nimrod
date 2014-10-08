<?php
require_once 'phplibs/functions.php';
require_once 'phplibs/class.nimrod.poutil.php';
require_once 'phplibs/class.nimrod.logger.php';

class AdminAction
{
  const DOWNLOAD  = "download";
  const DELETE    = "delete";
  const REARRANGE = "rearrange";
}

class AdminContrib
{
  const MINE  = "mine";
  const FULL  = "full";
}

function report_msg($msg) {
  $admin_link = '<a href="">' . __('Go back to admin panel', 'nimrod') . '</a>';
  return '<p>' . sprintf('%s &there4; %s', $msg, $admin_link) . '</p>';
}

// In *any* case this array must exist
if ( !isset($_POST['src-files']) ) {
  die( report_msg(__('No files were selected!', 'nimrod')) );
}
$src_files = explode(",", $_POST['src-files']);

if ( isset($_POST['confirm-delete']) ) {

  if ( intval($_POST['confirm-delete']) === 1 ) {
    
    foreach ($src_files as $rel_dir) {
      $abs_dir = GETTEXT_LOG_UNTRANSLATED . '/' . $rel_dir;
      $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($abs_dir), 
                    RecursiveIteratorIterator::CHILD_FIRST
      );
      foreach ($iterator as $name => $item) {
        $fnam = $item->getFilename();
        $path = $item->getPathname();
        if ( $fnam == '.' || $fnam == '..' ) continue;
        if ( $item->isDir($path) ) rmdir( $path );
        else unlink( $path );
      }
      rmdir($abs_dir);
    }
    die( report_msg(__('File deletion completed', 'nimrod')) );
  } else {
    die( report_msg(__('File deletion cancelled', 'nimrod')) );
  }
  
}

// Default options
$action  = AdminAction::REARRANGE;
$contrib = AdminContrib::MINE;

$bulk_action = FALSE;
if ( !isset($_POST['action-rearrange'])  ) {

  $bulk_action = TRUE;
  if ( !empty($_POST['action-top'])  ) {
    $action = $_POST['action-top'];
  } elseif ( !empty($_POST['action-bottom'])  ) {
    $action = $_POST['action-bottom'];
  }
  
}

if ( !empty($_POST['contrib-top'])  ) {
  $contrib = $_POST['contrib-top'];
} elseif ( !empty($_POST['contrib-bottom'])  ) {
  $contrib = $_POST['contrib-bottom'];
}

if ( $bulk_action ) {

  foreach ( $_POST['src-files'] as $src => $csv ) {
    $hash  = $_POST['src-hashes'][$src];
    $npots = explode(',', $csv);
    foreach ($npots as $pot_name) {
      $bulk_collection[] = NimrodLogger::getUid() . '/' . $hash . '/' . $pot_name;
    }
  }
  

} else {

  $dir_hashes = array();
  foreach ( $_POST['src-files'] as $src => $csv ) {
    $dir_hashes[] = $_POST['src-hashes'][$src];
  }

  $contrib_dir = GETTEXT_LOG_UNTRANSLATED;
  if ( $contrib == AdminContrib::MINE ) $contrib_dir .= '/' . NimrodLogger::getUid();

  $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($contrib_dir), 
                RecursiveIteratorIterator::SELF_FIRST
  );
  
  foreach ($iterator as $name => $item) {
    $file_path = $item->getPathname();
    if ( is_file($file_path) ) {
      $ext = pathinfo($file_path, PATHINFO_EXTENSION);
      if ($ext == 'npot') {
        $pot_name = basename($file_path);
        $dir_hash = basename( dirname($file_path) );
        if ( in_array($dir_hash, $dir_hashes) ) {
          if ( $action == AdminAction::REARRANGE /*|| $action == AdminAction::DOWNLOAD*/ ) {
            $npo = new NimrodPOUtil;
            $npo->read($file_path);
            if ( isset($npot_collection[$pot_name]) ) {
              $npot_collection[$pot_name]->update( $npo->db );
            }
          }
          $npot_collection[$pot_name] = $npo;
        }
      }
    }
  }

  // Sanitize and normalize feature weights.
  $weights = array();
  foreach ( $_POST['sort-feats'] as $feat => $val ) {
    $val = intval($val);
    if ( $val > 100 ) $val = 100;
    elseif ( $val < -100 ) $val = -100;
    $weights[$feat] = $val/100;
  }
  
}

$pot_files = $bulk_action ? $bulk_collection : array_keys($npot_collection);
$num_files = count($pot_files);
echo '<p>' . sprintf( __('You have chosen to %1$s %2$d Nimrod POT %3$s for all of the previously selected source files. There you are:'), 
              $action, $num_files, pluralize($num_files, 'file') ) . '</p>';

$links = array();
$paths = array();

if ( $bulk_action ) {

  if ( $action == AdminAction::DOWNLOAD ) {
    $zip = new ZipArchive;
    $user_dir = GETTEXT_LOG_UNTRANSLATED . '/' . NimrodLogger::getUid();
    $zip_name = 'npot-download.zip';
    $zip_path = $user_dir . '/' . $zip_name;
    $zip_link = plugin_dir_url($zip_path) . $zip_name;
    if ( !$zip->open( $zip_path, ZIPARCHIVE::OVERWRITE ) ) {
      die( report_msg(sprintf(__('There was an error when creating the ZIP file <tt>%s</tt>', 'nimrod'), $zip_path)) );
    }
  }
  
  $show = array();
  foreach ($bulk_collection as $rel_path) {
    $npot_name = basename($rel_path);
    $hash_name = dirname($rel_path);
    $url = plugin_dir_url($rel_path) . $npot_name;
    $links[] = $url;
    $paths[] = $hash_name;
    $show[]  = '<a href="' . $url . '">' . $npot_name . '</a>';
    if ( $action == AdminAction::DOWNLOAD ) {
      $abs_path = GETTEXT_LOG_UNTRANSLATED . '/' . $rel_path;
      if ( !$zip->addFile($abs_path, $rel_path) ) {
        die( report_msg(sprintf(__('There was an error when adding the file <tt>%s</tt>', 'nimrod'), $abs_path)) );
      }
    }
  }
  
  if ( $action == AdminAction::DOWNLOAD ) $zip->close();
  else echo '<p>' . implode(', ', $show) . '</p>';
  
} else {

  echo '<ul>';
  foreach ($npot_collection as $pot_name => $npo) {
    $out_file = $contrib_dir .'/' . $pot_name;
    if ( $action == AdminAction::REARRANGE ) {
      $npo->sort($weights)->write($out_file);
    }
    /*
    elseif ( $action == AdminAction::DOWNLOAD ) {
      $npo->write($out_file);
    }
    */
    $url = plugin_dir_url($out_file) . $pot_name;
    $links[] = $url;
    $paths[] = GETTEXT_LOG_UNTRANSLATED . '/' . $pot_name;
    echo '<li><a href="' . $url . '">' . $pot_name . '</a></li>';
  }
  echo '</ul>';
  report_msg(__('Done.', 'nimrod'), $abs_path);
}

$csv_links = implode( ',', array_unique($links) );
$csv_paths = implode( ',', array_unique($paths) );
?>

<?php if ( $action == AdminAction::DOWNLOAD ): ?>

  <?php echo report_msg(sprintf(__('A <a href="%s">ZIP file</a> has been created for your convenience.', 'nimrod'), $zip_link)); ?>

<?php elseif ( $action == AdminAction::DELETE ): ?>

  <form action="" method="post">
    <strong><?php echo __('Are you sure?', 'nimrod'); ?></strong>
    <input type="radio" name="confirm-delete" value="1" checked="checked" /> <?php echo __('Yes', 'nimrod'); ?>
    <input type="radio" name="confirm-delete" value="0" /> <?php echo __('No', 'nimrod'); ?>
    <input type="hidden" name="src-files" value="<?php echo $csv_paths; ?>" />
    <input type="submit" value="<?php echo __('Confirm', 'nimrod'); ?>" />
  </form>

<?php elseif ( $action == AdminAction::REARRANGE && function_exists('curl_init') ): ?>

  <?php if ( get_option('nimrod_contrib') == "off" ): ?>

    <h2><?php echo __('Science Anyone?', 'nimrod'); ?></h2>
    <p>
      <?php echo sprintf(__('You can contribute to improving this plugin and advance research by checking the <tt>Contribute</tt> checkbox in the <a href="%s">Settings</a> page.', 'nimrod'), admin_url('options-general.php?page=nimrod_settings')); ?>
    </p>
    <p>
      <?php echo __('Note that your data is completely anonymous and will not be shared with third parties. We will analyze the data for research purposes. <em>Thank you in advance!</em>'); ?>
    </p>
  
  <?php else: ?>

    <?php
    $feats = array();
    foreach ($_COOKIE as $key => $value) {
      if ( str_startswith($key, 'nimrod-feat') ) {
        $feats[$key] = $value;
      }
    }
    
    $post_data = array(
      'feats' => json_encode($feats), 
      'files' => json_encode($src_files),
      'lang'  => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
      'url'   => $_SERVER['REQUEST_URI'],
      'uid'   => NimrodLogger::getUid(),
    );
    $request = http_request(
                'http://kant3.prhlt.upv.es/nimrod/save-contrib.php',
                array(
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => $post_data
                     )
               );
#    $msg = $transfer['errnum'] > 0 ? $request['errmsg'] : $request['content'];
#    echo '<h3>' . $msg . '</h3>';
    ?>
    
  <?php endif; ?>
  
<?php endif; ?>
