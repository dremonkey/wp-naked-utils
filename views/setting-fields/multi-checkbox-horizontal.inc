<?php 
/**
 * Template for a form horizontal multi-checkbox field
 */
?>

<div class="field-wrapper checkboxes horizontal">

	<?php foreach( $options as $i=>$option ) : ?>
		
		<?php $checked = in_array( $option, $saved_options ) ? 'checked' : ''; ?>

		<span style="margin-right: 20px;">
			<input name="<?php echo $name . '[' . $i . ']' ?>" type="checkbox" value="<?php echo $option ?>" <?php echo $checked ?> />
			<label for="<?php echo $name . '[' . $i . ']' ?>">
				<?php echo $option ?>
			</label>
		</span>
			
	 <?php endforeach; ?>

	 <label style="display: block; margin-bottom:5px;" for="<?php echo $name ?>">
		<?php echo $desc ?>
	</label>

</div>