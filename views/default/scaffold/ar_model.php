<?php echo '<?php' ?>

class Model_<?php echo ucfirst($name); ?> extends Orm\Model {
<?php if (isset($table)): ?>
	protected static $_table_name = '<?php echo $table; ?>';
<?php endif; ?>
<?php if ( ! \Cli::option('no-timestamp', false)): ?>
	protected static $_observers = array(
		'Orm\\Observer_CreatedAt' => array('before_insert'),
		'Orm\\Observer_UpdatedAt' => array('before_save'),
	);
<?php endif; ?>

}

/* End of file <?php echo Str::lower($name); ?>.php */