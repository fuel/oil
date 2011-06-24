<?php foreach ($fields as $field): ?>
<p>
	<strong><?php echo \Inflector::humanize($field['name']); ?>:</strong>
	<?php echo '<?php'; ?> echo $<?php echo $singular.'->'.$field['name']; ?>; <?php echo '?>'; ?>
</p>
<?php endforeach; ?>

<?php echo '<?php'; ?> echo Html::anchor('<?php echo $controller_uri ?>/edit/'.$<?php echo $singular; ?>->id, 'Edit'); <?php echo '?>'; ?> | 
<?php echo '<?php'; ?> echo Html::anchor('<?php echo $controller_uri ?>', 'Back'); <?php echo '?>'; ?>
