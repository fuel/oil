<h2 class="first">Listing <?php echo ucfirst($plural); ?></h2>

<table cellspacing="0">
	<tr>
<?php foreach ($fields as $field): ?>
		<th><?php echo \Inflector::humanize($field['name']); ?></th>
<?php endforeach; ?>
		<th></th>
	</tr>

	<?php echo '<?php'; ?> foreach ($<?php echo $plural; ?> as $<?php echo $singular; ?>): <?php echo '?>'; ?>
	<tr>

<?php foreach ($fields as $field): ?>
		<td><?php echo '<?php'; ?> echo $<?php echo $singular.'->'.$field['name']; ?>; <?php echo '?>'; ?></td>
<?php endforeach; ?>
		<td>
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>/view/'.$<?php echo $singular; ?>->id, 'View'); <?php echo '?>'; ?> |
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>/edit/'.$<?php echo $singular; ?>->id, 'Edit'); <?php echo '?>'; ?> |
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>/delete/'.$<?php echo $singular; ?>->id, 'Delete', array('onclick' => "return confirm('Are you sure?')")); <?php echo '?>'; ?>
		</td>
	</tr>
	<?php echo '<?php endforeach; ?>'; ?>
</table>

<br />

<?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>/create', 'Add new <?php echo \Inflector::humanize($singular); ?>'); <?php echo '?>'; ?>