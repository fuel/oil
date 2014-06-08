<h2>Viewing #<?php echo $article->id; ?></h2>

<p>
	<strong>Title:</strong>
	<?php echo $article->title; ?></p>
<p>
	<strong>Body:</strong>
	<?php echo $article->body; ?></p>

<?php echo Html::anchor('admin/article/edit/'.$article->id, 'Edit'); ?> |
<?php echo Html::anchor('admin/article', 'Back'); ?>