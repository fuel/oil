<?php echo '<?php' ?>

use Orm\Model;

class Model_<?php echo $model_name; ?> extends Model
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

	public static function validate($factory)
	{
		$val = Validation::forge($factory);

<?php foreach ($fields as $field): ?>
<?php
		$rules = array('required');
		
		if ($field['name'] === 'email')
		{
			$rules[] = 'valid_email';
			$rules[] = !is_null($field['constraint']) ? "max_length[{$field['constraint']}]" : 'max_length[255]';
		}
		
		elseif ($field['type'] === 'varchar' or $field['type'] === 'string')
		{
			$rules[] = 'valid_string';
			$rules[] = !is_null($field['constraint']) ? "max_length[{$field['constraint']}]" : 'max_length[255]';
		}
		
		elseif ($field['type'] === 'int' or $field['type'] === 'intenger')
		{
			$rules[] = 'is_int';
		}
		
		$rules = implode('|', $rules);
?>
		$val->add_field('<?php echo $field['name']; ?>', '<?php echo ucwords(str_replace('_', ' ', $field['name'])); ?>', '<?php echo $rules; ?>');
<?php endforeach; ?>

		return $val;
	}

}
