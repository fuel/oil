<?php echo '<?php' ?>

class <?php echo \Config::get('controller_prefix', 'Controller_').$controller_name ?> extends <?php echo \Cli::option('extends', $controller_parent).PHP_EOL ?>
{

<?php foreach ($actions as $action): ?>
	public function action_<?php echo "{$action['name']}({$action['params']})".PHP_EOL ?>
	{
<?php echo $action['code'] ?>
	}

<?php endforeach ?>
}
