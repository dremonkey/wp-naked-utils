<?php
/**
 * Template for a standard form select field
 */
?>

<select name="<?php echo $name ?>">

	<?php foreach ( $options as $key=>$option ) : ?>
		
		<?php $selected = $value == $key ? 'selected="selected"' : ''; ?>
		<option value='<?php echo $key ?>' <?php echo $selected ?> >
			<?php echo $option ?>
		</option>

	<?php endforeach; ?>

</select>

<p class="description" for="<?php echo $name ?>">
	<?php echo $desc ?>
</p>
	