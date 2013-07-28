<?php 
/**
 * Standard text input field with a description
 */
?>

<?php $cols = isset( $cols ) ? $cols : 50; ?>
<?php $rows = isset( $rows ) ? $rows : 5; ?>

<textarea id="<?php echo $id ?>" name="<?php echo $name ?>" cols="<?php echo $cols ?>" rows="<?php echo $rows ?>"><?php echo $value ?></textarea>
<p class="description"><?php echo $desc ?></p>