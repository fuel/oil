<h2>Editing Article</h2>
<br>

<?php echo render('admin/article/_form'); ?>
<p>
	<?php echo Html::anchor('admin/article/view/'.$article->id, 'View'); ?> |
	<?php echo Html::anchor('admin/article', 'Back'); ?></p>
