<h2>Listing <?php echo \Str::ucfirst($plural_name); ?></h2>
<br>

<?php echo "<?php if (\${$plural_name}): ?>".PHP_EOL; ?>
	<div class="table-responsive">
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
				<?php echo '<?php'; ?> foreach ($<?php echo $plural_name; ?> as $item): <?php echo '?>'.PHP_EOL; ?>
					<tr>
<?php foreach ($fields as $field): ?>
						<td><?php echo '<?php'; ?> echo $item<?php echo '->'.$field['name']; ?>; <?php echo '?>'; ?></td>
<?php endforeach; ?>

						<td>
							<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/view/'.$item->id, 'View'); <?php echo '?>'; ?> |
							<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/edit/'.$item->id, 'Edit'); <?php echo '?>'; ?> |
							<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/delete/'.$item->id, 'Delete', array('onclick' => "return confirm('Are you sure?')")); <?php echo '?>'.PHP_EOL; ?>
						</td>
					</tr>
				<?php echo '<?php endforeach; ?>'.PHP_EOL; ?>
			</tbody>
		</table>
	</div>

	<?php echo '<?php'; ?> echo $pagination <?php echo '?>'.PHP_EOL; ?>
<?php echo '<?php else: ?>'.PHP_EOL; ?>
	<p>No <?php echo \Str::ucfirst($plural_name); ?>.</p>
<?php echo '<?php endif; ?>'.PHP_EOL; ?>

<p>
	<?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri; ?>/create', 'Add new <?php echo \Inflector::humanize($singular_name); ?>', array('class' => 'btn btn-success')); <?php echo '?>'.PHP_EOL; ?>
</p>
