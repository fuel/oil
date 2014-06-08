<?php echo '<?php'."\n"; ?>

<?php
	if ($namespace !== '')
	{
		echo $namespace."\n\n";
	}
?>
class Controller_<?php echo $controller_name; ?> extends <?php echo \Cli::option('extends', $controller_parent)."\n"; ?>
{

<?php foreach ($actions as $action): ?>
	public function action_<?php echo $action['name']; ?>(<?php echo $action['params']; ?>)
	{
<?php echo $action['code']."\n"; ?>
	}

<?php endforeach; ?>
}
