<?php echo '<?php' ?>

class Model_<?php echo ucfirst($name); ?> extends Orm\Model {
<?php if (isset($table)): ?>
	protected static $_table_name = '<?php echo $table; ?>';
<?php endif; ?>

}

/* End of file <?php echo Str::lower($name); ?>.php */