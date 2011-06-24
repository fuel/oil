<h2 class="first">Editing <?php echo \Str::ucfirst($singular); ?></h2>

<?php echo '<?php'; ?> echo render('<?php echo $controller_uri; ?>/_form'); ?>
<br />
<p>
<?php echo '<?php'; ?> echo Html::anchor('<?php echo $controller_uri; ?>/view/'.$<?php echo $singular; ?>->id, 'View'); <?php echo '?>'; ?> |
<?php echo '<?php'; ?> echo Html::anchor('<?php echo $controller_uri; ?>', 'Back'); <?php echo '?>'; ?>
</p>
