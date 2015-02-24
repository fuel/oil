<h2>Listing <span class='muted'><?php echo \Str::ucfirst($plural_name); ?></span></h2>
<br>
<?php echo "<?php if (\${$plural_name}): ?>"; ?>

<table class="table table-striped">
	<thead>
		<tr>
<?php foreach ($fields as $field): ?>
			<th><?php echo \Inflector::humanize($field['name']); ?></th>
<?php endforeach; ?>
			<th>&nbsp;</th>
		</tr>
	</thead>
	<tbody>
<?php echo '<?php'; ?> foreach ($<?php echo $plural_name; ?> as $item): <?php echo '?>'; ?>
		<tr>

<?php foreach ($fields as $field): ?>
			<td><?php echo '<?php'; ?> echo $item<?php echo '->'.$field['name']; ?>; <?php echo '?>'; ?></td>
<?php endforeach; ?>
			<td>
				<div class="btn-toolbar">
					<div class="btn-group">
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/view/'.$item->id, '<span class="glyphicon glyphicon-eye-open"></span> View', array('class' => 'btn btn-default btn-sm')); <?php echo '?>'; ?>
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/edit/'.$item->id, '<span class="glyphicon glyphicon-wrench"></span> Edit', array('class' => 'btn btn-default btn-sm')); <?php echo '?>'; ?>
						<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/delete/'.$item->id, '<span class="glyphicon glyphicon-trash"></span> Delete', array('class' => 'btn btn-sm btn-danger', 'onclick' => "return confirm('Are you sure?')")); <?php echo '?>'; ?>
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
