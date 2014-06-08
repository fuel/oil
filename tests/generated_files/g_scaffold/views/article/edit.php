<h2>Editing <span class='muted'>Article</span></h2>
<br>

<?php echo render('article/_form'); ?>
<p>
	<?php echo Html::anchor('article/view/'.$article->id, 'View'); ?> |
	<?php echo Html::anchor('article', 'Back'); ?></p>
