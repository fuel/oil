<h2 class="first">Listing <?php echo \Str::ucfirst($plural_name); ?></h2>

<?php echo "<?php if (\${$plural_name}): ?>"; ?>

<table cellspacing="0">
	<tr>
<?php foreach ($fields as $field): ?>
		<th><?php echo \Inflector::humanize($field['name']); ?></th>
<?php endforeach; ?>
		<th></th>
	</tr>

	<?php echo '<?php'; ?> foreach ($<?php echo $plural_name; ?> as $<?php echo $singular_name; ?>): <?php echo '?>'; ?>
	<tr>

<?php foreach ($fields as $field): ?>
		<td><?php echo '<?php'; ?> echo $<?php echo $singular_name.'->'.$field['name']; ?>; <?php echo '?>'; ?></td>
<?php endforeach; ?>
		<td>
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/view/'.$<?php echo $singular_name; ?>->id, 'View'); <?php echo '?>'; ?> |
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/edit/'.$<?php echo $singular_name; ?>->id, 'Edit'); <?php echo '?>'; ?> |
			<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/delete/'.$<?php echo $singular_name; ?>->id, 'Delete', array('onclick' => "return confirm('Are you sure?')")); <?php echo '?>'; ?>
		</td>
	</tr>
	<?php echo '<?php endforeach; ?>'; ?>
</table>

<?php echo '<?php else: ?>'; ?>

<p>No Entries.</p>

<?php echo '<?php endif; ?>'; ?>

<br />

<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/create', 'Add new <?php echo \Inflector::humanize($singular_name); ?>'); <?php echo '?>'; ?>
