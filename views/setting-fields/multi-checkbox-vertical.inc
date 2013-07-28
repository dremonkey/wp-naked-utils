<?php 
/**
 * Template for a form vertical multi-checkbox field
 */
?>

<div class="field-wrapper checkboxes vertical">

	<label style="display: block; margin-bottom:5px;" for="<?php echo $name ?>">
		<?php echo $desc ?>
	</label>

	<?php foreach( $options as $i=>$option ) : ?>
		
		<?php $checked = in_array( $option, $saved_options ) ? 'checked' : ''; ?>

		<span style="display: block;">
			<input name="<?php echo $name . '[' . $i . ']' ?>" type="checkbox" value="<?php echo $option ?>" <?php echo $checked ?> />
			<label for="<?php echo $name . '[' . $i . ']' ?>">
				<?php echo $option ?>
			</label>
		</span>
			
	 <?php endforeach; ?>
</div>