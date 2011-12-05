<?php echo '<?php' ?>

class Model_<?php echo $model_name; ?> extends \Orm\Model
{
	protected static $_properties = array(
		'id',
<?php foreach ($fields as $field): ?>
		'<?php echo $field['name']; ?>',
<?php endforeach; ?>
<?php if ($include_timestamps): ?>
		'created_at',
		'updated_at',
<?php endif; ?>
	);

<?php if ($include_timestamps): ?>
	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
		),
	);
<?php endif; ?>

}
