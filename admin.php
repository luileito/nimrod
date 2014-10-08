<?php
wp_enqueue_style( 'admin-css',     plugins_url('css/admin.css', __FILE__) );
wp_enqueue_style( 'jquery-ui-css', plugins_url('css/jquery-ui.css', __FILE__) );

wp_enqueue_script( 'jquery-ui-js',  plugins_url('jslibs/jquery-ui.js', __FILE__),     array('jquery') );
wp_enqueue_script( 'jquery-cookie', plugins_url('jslibs/jquery-cookie.js', __FILE__), array('jquery') );
?>

<div class="wrap">

<?php screen_icon(); ?>
<h2><?php echo __("Nimrod's admin panel", 'nimrod') ?></h2>

<?php if ( !empty($_POST) ): ?>

  <?php include 'admin-save.php'; ?>

<?php else: ?>

  <form method="post" action="">
    <?php 
    include 'admin-mixer.php'; 
    include 'admin-table.php'; 
    ?>
    <input type="submit" class="button-primary" name="action-rearrange" value="<?php echo __('Rearrange gettext messages', 'nimrod') ?>" />
  </form>

<?php endif; ?>

</div><!-- .wrap -->
