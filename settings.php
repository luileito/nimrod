<?php
register_setting( 'nimrod_settings', 'nimrod_dir' );

add_settings_section( 'setting_choose_dir', __('Directory to save POT files', 'nimrod'), 'n_print_info_dir', 'nimrod_settings' );
add_settings_field( 'nimrod_dir', __('Directory path:', 'nimrod'), 'n_create_field_dir', 'nimrod_settings', 'setting_choose_dir' );

add_settings_section( 'setting_choose_logo', __('Nimrod logo', 'nimrod'), 'n_print_info_logo', 'nimrod_settings' );
add_settings_field( 'nimrod_logo', __('Display logo:', 'nimrod'), 'n_create_field_logo', 'nimrod_settings', 'setting_choose_logo' );

add_settings_section( 'setting_choose_contrib', __('Contribute to improving Nimrod', 'nimrod'), 'n_print_info_contrib', 'nimrod_settings' );
add_settings_field( 'nimrod_contrib', __('Contribute:', 'nimrod'), 'n_create_field_contrib', 'nimrod_settings', 'setting_choose_contrib' );

function n_print_info_dir() {
  echo __('This directory <em>must be writable</em> by PHP.', 'nimrod') . PHP_EOL;
  echo sprintf(__('It can be <em>absolute</em> (starts with <tt>/</tt>) or <em>relative</em> to <tt>%s/</tt>', 'nimrod'), dirname( __FILE__ ) ) . PHP_EOL;
}

function n_create_field_dir() {
  echo '<input type="text" id="nimrod_dir" name="nimrod_dir" value="' . get_option('nimrod_dir') . '" /> ';
  echo __('If it does not exist, PHP will try to create it.', 'nimrod');
}

function n_print_info_logo() {
  echo __('You can turn the logo on/off. It appears in the bottom-left corner of your admin pages.', 'nimrod');
}

function n_create_field_logo() {
  $checked = NULL;
  if ( get_option('nimrod_logo') != "off" ) {
    $checked = 'checked="checked"';
  }
  echo '<input type="checkbox" id="nimrod_logo" name="nimrod_logo" ' . $checked . '" /> ';
}

function n_print_info_contrib() {
  echo __('Let us collect and analyze your Nimrod settings periodically.', 'nimrod');
}

function n_create_field_contrib() {
  $checked = NULL;
  if ( get_option('nimrod_contrib') != "off" ) {
    $checked = 'checked="checked"';
  }
  echo '<input type="checkbox" id="nimrod_contrib" name="nimrod_contrib" ' . $checked . '" /> ';
}
?>


<div class="wrap">
  <?php screen_icon(); ?>
  <h2><?php echo __('Nimrod settings', 'nimrod') ?></h2>

  <?php
  if ( !empty($_POST) ) {
    include plugin_dir_path(__FILE__) . 'settings-save.php';
  }
  ?>
  
  <form method="post" action="">
    <?php
    settings_fields( 'nimrod_settings' );
    do_settings_sections( 'nimrod_settings' );
    submit_button( __("Update", 'nimrod') );
    ?>
  </form>
</div><!-- .wrap -->
