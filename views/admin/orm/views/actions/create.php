<h2>New <?php echo \Str::ucfirst($singular_name); ?></h2>
<br>

<div class="row">
	<div class="col-md-6">
		<?php echo '<?php'; ?> echo render('<?php echo $view_path; ?>/_form'); ?>
	</div>
</div>

<p><?php echo '<?php'; ?> echo Html::anchor('<?php echo $uri ?>', 'Back'); <?php echo '?>'; ?></p>
