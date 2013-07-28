<?php
/**
 * Template for a standard form checkbox field
 */
?>

<?php $checked = checked( '1', $value, false ); ?>

<label class="description" for="<?php echo $name ?>" style="display:block;margin-bottom:10px;">
	<input name="<?php echo $name ?>" type="checkbox" value="1" <?php echo $checked ?> />
	<?php echo $desc ?>
</label>