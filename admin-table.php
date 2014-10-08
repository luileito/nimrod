<?php 
require_once 'phplibs/functions.php';
require_once 'phplibs/class.nimrod.logger.php';
?>

<fieldset class="nimrod-fld">
  <legend><?php echo __("My localization files", 'nimrod') ?></legend>

  <?php
  function table_actions( $placement ) {
    // $placement can be either "top" or "bottom"
    $commands  = '<div class="tablenav top">';
    $commands .=   '<div class="alignleft actions">';
    $commands .=   '<select name="action-'.$placement.'">';
    $commands .=      '<option value="">' . __("Bulk Actions", 'nimrod') . '</option>';
    $commands .=      '<option value="download">' . __("Download", 'nimrod') . '</option>';
    $commands .=      '<option value="delete">' . __("Delete", 'nimrod') . '</option>';
    $commands .=    '</select>';
    $commands .=    '<input type="submit" id="doaction" class="button-secondary action" value="' . __("Apply", 'nimrod') . '">';
    $commands .=  '</div>';
    $commands .=  '<div class="alignleft actions">';
    $commands .=     '<select name="contrib-'.$placement.'">';
    $commands .=      '<option value="">' . __("Contribution type", 'nimrod') . '</option>';
    $commands .=      '<option value="mine">' . __("Mine only", 'nimrod') . '</option>';
    $commands .=      '<option value="full">' . __("From everyone", 'nimrod') . '</option>';
    $commands .=   '</select>';
    $commands .=   '</div>';
    $commands .=   '<br class="clear"/>';
    $commands .= '</div>';
    return $commands;
  }
  $table_headers  = '<th scope="col" id="cb" class="manage-column column-cb check-column"><input type="checkbox"></th>';
  $table_headers .= '<th scope="col" id="title" class="manage-column">' . __("Source file", 'nimrod') . '</th>';
  $table_headers .= '<th scope="col" id="author" class="manage-column">' . __("Number of resource strings", 'nimrod') . '</th>';
  $table_headers .= '<th scope="col" id="categories" class="manage-column">' . __("Associated POT files", 'nimrod') . '</th>';
  ?>

  <?php echo table_actions('top'); ?>
  <table class="widefat fixed" cellspacing="0">
  <thead><tr><?php echo $table_headers; ?></tr></thead>
  <tfoot><tr><?php echo $table_headers; ?></tr></tfoot>
  <tbody>
  <?php 
  if ( !defined('GETTEXT_LOG_UNTRANSLATED') ) {
    echo '<td colspan="4">' . sprintf(__('No directory specified. Go and set one at <a href="%s">the settings page</a>.', 'nimrod'), 'options-general.php?page=nimrod_settings') . '</td>';
  } else {
    $iterator = new RecursiveIteratorIterator(
                  new RecursiveDirectoryIterator(GETTEXT_LOG_UNTRANSLATED), 
                  RecursiveIteratorIterator::SELF_FIRST
                );
    $num_contributors = 0;
    foreach ($iterator as $name => $item) {
      if ( is_file($item->getPathname()) ) {
        $clean_path = substr( $item->getPathname(), strlen(GETTEXT_LOG_UNTRANSLATED) + 1 );
        $file_name  = basename($clean_path);
        if ($file_name == 'meta.json') {
          $uid = NimrodLogger::getUid();
          if ( str_startswith($clean_path, $uid) ) {
            $json_data = file_get_contents( $item->getPathname() );
            $json = json_decode( $json_data, true );
            echo '<tr valign="top">';
            foreach ($json as $hash => $info) {
              echo '<th scope="row" class="check-column">';
              echo  '<input type="checkbox" name="src-files[' . $info['path'] . ']" value="' . implode(',', $info['npot']) . '" /> ';
              echo  '<input type="hidden" name="src-hashes[' . $info['path'] . ']" value="' . $hash . '" /> ';
              echo '</th>';
              echo '<td><a href="' . site_url() . '/'. $info['path'] . '">' . $info['path'] . '</a></td>';
              echo '<td>' . $info['nmsg'] . '</td>';
              echo '<td>';
              $pot_url = plugin_dir_url(__FILE__) . basename(GETTEXT_LOG_UNTRANSLATED) . '/' . $uid . '/' . $hash . '/' . $npot;
              $map_params = array_fill(0, count($info['npot']), $pot_url);
              $npot_links = array_map( 'linkify', $info['npot'], $map_params );
              echo implode(' | ', $npot_links);
              echo '</td>';
            }
          } else {
            $num_contributors++;
          }
        }
      }
    }
    echo '</tr>';      
#      if ( $num_contributors > 0 ) {
#        $contrib_msg  = '<input type="checkbox" name="merge-all" /> ';
#        $contrib_msg .= sprintf(__('Merge data from all user contributions (%d users).', 'nimrod'), $num_contributors);
#      } else {
#        $contrib_msg = __('There are no user contributions yet.', 'nimrod);
#      }
#      echo '<p>' . $contrib_msg . '</p>';
  }
  ?>
  </tbody>
  </table>
  <?php echo table_actions('bottom'); ?>
    
</fieldset>
