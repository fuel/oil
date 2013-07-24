<h2>Listing <?php echo \Str::ucfirst($plural_name); ?></h2>
<br>
<?php echo "<?php if (\${$plural_name}): ?>"; ?>

<table class="table table-striped">
	<thead>
		<tr>
<?php foreach ($fields as $field): ?>
			<th><?php echo \Inflector::humanize($field['name']); ?></th>
<?php endforeach; ?>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php echo '<?php'; ?> foreach ($<?php echo $plural_name; ?> as $<?php echo $singular_name; ?>): <?php echo '?>'; ?>
		<tr>

<?php foreach ($fields as $field): ?>
			<td><?php echo '<?php'; ?> echo $<?php echo $singular_name.'->'.$field['name']; ?>; <?php echo '?>'; ?></td>
<?php endforeach; ?>
			<td>
				<div class="btn-toolbar">
					<div class="btn-group">
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/view/'.$<?php echo $singular_name; ?>->id, '<i class="icon-eye-open"></i> View', array('class' => 'btn btn-small')); <?php echo '?>'; ?>
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/edit/'.$<?php echo $singular_name; ?>->id, '<i class="icon-wrench"></i> Edit', array('class' => 'btn btn-small')); <?php echo '?>'; ?>
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/delete/'.$<?php echo $singular_name; ?>->id, '<i class="icon-trash icon-white"></i> Delete', array('class' => 'btn btn-small btn-danger', 'onclick' => "return confirm('Are you sure?')")); <?php echo '?>'; ?>
					</div>
				</div>

			</td>
		</tr>
<?php echo '<?php endforeach; ?>'; ?>
	</tbody>
</table>

<?php echo '<?php else: ?>'; ?>

<p>No <?php echo \Str::ucfirst($plural_name); ?>.</p>

<?php echo '<?php endif; ?>'; ?>
<p>
	<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/create', 'Add new <?php echo \Inflector::humanize($singular_name); ?>', array('class' => 'btn btn-success')); <?php echo '?>'; ?>


</p>
