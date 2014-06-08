<h2>Listing Articles</h2>
<br>
<?php if ($articles): ?>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Title</th>
			<th>Body</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($articles as $item): ?>		<tr>

			<td><?php echo $item->title; ?></td>
			<td><?php echo $item->body; ?></td>
			<td>
				<?php echo Html::anchor('admin/article/view/'.$item->id, 'View'); ?> |
				<?php echo Html::anchor('admin/article/edit/'.$item->id, 'Edit'); ?> |
				<?php echo Html::anchor('admin/article/delete/'.$item->id, 'Delete', array('onclick' => "return confirm('Are you sure?')")); ?>

			</td>
		</tr>
<?php endforeach; ?>	</tbody>
</table>

<?php else: ?>
<p>No Articles.</p>

<?php endif; ?><p>
	<?php echo Html::anchor('admin/article/create', 'Add new Article', array('class' => 'btn btn-success')); ?>

</p>
