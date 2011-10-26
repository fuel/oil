<?php echo '<?php' ?>

class Model_<?php echo $model_class; ?> extends Model_Crud
{
	protected static $_table_name = '<?php echo $table; ?>';
}
