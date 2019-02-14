<h2>Viewing #<?php echo '<?php'; ?> echo $<?php echo $singular_name; ?>->id; <?php echo '?>'; ?></h2>
<br>

<dl class="dl-horizontal">
<?php foreach ($fields as $field): ?>
	<dt><?php echo \Inflector::humanize($field['name']); ?></dt>
	<dd><?php echo '<?php'; ?> echo $<?php echo $singular_name.'->'.$field['name']; ?>; <?php echo '?>'; ?></dd>
	<br>
<?php endforeach; ?>
</dl>

<div class="btn-group">
	<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri ?>/edit/'.$<?php echo $singular_name; ?>->id, 'Edit', array('class' => 'btn btn-warning')); <?php echo '?>'.PHP_EOL; ?>
	<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri ?>', 'Back', array('class' => 'btn btn-default')); <?php echo '?>'.PHP_EOL; ?>
</div>
