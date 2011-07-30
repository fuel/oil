<h2 class="first">New <?php echo \Str::ucfirst($singular); ?></h2>

<?php echo '<?php'; ?> echo render('<?php echo $controller_uri ?>/_form'); ?>
<br />
<p><?php echo '<?php'; ?> echo Html::anchor('<?php echo $controller_uri ?>', 'Back'); <?php echo '?>'; ?></p>
