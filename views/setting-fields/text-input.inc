<?php 
/**
 * Standard text input field with a description
 */
?>

<?php $size = isset( $size ) ? $size : 50; ?>

<input type="text" id="<?php echo $id ?>" name="<?php echo $name ?>" value="<?php echo $value ?>" size="<?php echo $size ?>"/>
<p class="description"><?php echo $desc ?></p>