<?php echo '<?php' ?>

use Orm\Model;

class Model_<?php echo $model_class; ?> extends Model
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
		'Orm\Observer_CreatedAt' => array('before_insert'),
		'Orm\Observer_UpdatedAt' => array('before_save'),
	);
<?php endif; ?>

}
