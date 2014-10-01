<?php
register_setting( 'nimrod_settings', 'nimrod_dir' );

add_settings_section( 'setting_choose_dir', __('Directory to save POT files'), 'n_print_dir_info', 'nimrod_settings' );
add_settings_field( 'nimrod_dir', __('Directory path:'), 'n_create_field_dir', 'nimrod_settings', 'setting_choose_dir' );

add_settings_section( 'setting_choose_logo', __('Nimrod logo'), 'n_print_logo_info', 'nimrod_settings' );
add_settings_field( 'nimrod_logo', __('Display logo:'), 'n_create_field_logo', 'nimrod_settings', 'setting_choose_logo' );

function n_print_dir_info() {
  echo __('This directory <em>must be writable</em> by PHP.') . PHP_EOL;
  echo sprintf(__('It can be <em>absolute</em> (starts with <tt>/</tt>) or <em>relative</em> to <tt>%s/</tt>'), dirname( __FILE__ ) ) . PHP_EOL;
}

function n_create_field_dir() {
  echo '<input type="text" id="nimrod_dir" name="nimrod_dir" value="' . get_option('nimrod_dir') . '" /> ';
  echo __('If it does not exist, PHP will try to create it.');
}

function n_print_logo_info() {
  echo __('You can turn the logo on/off. It appears in the bottom-left corner of your admin pages.');
}

function n_create_field_logo() {
  $checked = NULL;
  if ( get_option('nimrod_logo') != "off" ) {
    $checked = 'checked="checked"';
  }
  echo '<input type="checkbox" id="nimrod_logo" name="nimrod_logo" ' . $checked . '" /> ';
}
?>


<div class="wrap">
  <?php screen_icon(); ?>
  <h2><?php echo __('Nimrod settings') ?></h2>

  <?php
  if ( !empty($_POST) ) {
    include plugin_dir_path(__FILE__) . 'settings-save.php';
  }
  ?>
  
  <form method="post" action="">
    <?php
    settings_fields( 'nimrod_settings' );
    do_settings_sections( 'nimrod_settings' );
    submit_button( __("Update") );
    ?>
  </form>
</div><!-- .wrap -->
