<h2 class="first">New <?php echo ucfirst($singular); ?></h2>

<?php echo '<?php'; ?> echo render('<?php echo $plural; ?>/_form'); ?>
<br />
<p><?php echo '<?php'; ?> echo Html::anchor('<?php echo $plural; ?>', 'Back'); <?php echo '?>'; ?></p>