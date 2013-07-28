<?php 
/**
 * Naked Features Settings page view (template).
 */
?>

<div class="wrap">
    <?php screen_icon(); ?>
   
   	<h2><?php echo $page_title ?></h2>

    <form method="post" action="options.php" style="clear:both">
      <?php settings_fields( $options_group ); ?>
      <?php do_settings_sections( $page_key ); ?>
      <?php submit_button(); ?>
    </form>
</div>