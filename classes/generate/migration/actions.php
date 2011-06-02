<?php
/**
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.0
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2011 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Oil;

/**
 * Oil\Generate_Migration_Actions
 * Handles actions for generating migrations in Oil
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Tom Arnfeld
 */
class Generate_Migration_Actions
{
	
	/**
	 * Each migration action should return an array with two items, 0 being the up and 1 the being down.
	 */
	
	// create_{tablename}
	public static function create($subjects, $fields)
	{
		$field_str = '';
		
		foreach($fields as $field)
		{
			$name = array_shift($field);
			
			$field_opts = array();
			foreach($field as $option => $val)
			{
				if($val === true)
				{
					$field_opts[] = "'$option' => true";
				}
				else
				{
					if(is_int($val))
					{
						$field_opts[] = "'$option' => $val";
					}
					else
					{
						$field_opts[] = "'$option' => '$val'";
					}
				}
			}
			$field_opts = implode(', ', $field_opts);
			
			$field_str .= "\t\t\t'$name' => array({$field_opts}),".PHP_EOL;			
		}
		
		// ID Field
 		$field_str = "\t\t\t'id' => array('constraint' => 11, 'type' => 'int', 'auto_increment' => true),".PHP_EOL . $field_str;

		if ( ! \Cli::option('no-timestamps', false))
		{
			$field_str .= "\t\t\t'created_at' => array('constraint' => 11, 'type' => 'int'),";
			$field_str .= PHP_EOL."\t\t\t'updated_at' => array('constraint' => 11, 'type' => 'int'),";
		}

		$up = <<<UP
		\DBUtil::create_table('{$subjects[1]}', array(
$field_str
		), array('id'));
UP;

		$down = <<<DOWN
		\DBUtil::drop_table('{$subjects[1]}');
DOWN;
		
		return array($up, $down);
	}
	
	// add_{thing}_to_{tablename}
	public static function add($subjects, $fields)
	{
		$field_up_str = '';
	
		foreach($fields as $field)
		{
			$name = array_shift($field);
			
			$field_opts = array();
			foreach($field as $option => $val)
			{
				if($val === true)
				{
					$field_opts[] = "'$option' => true";
				}
				else
				{
					if(is_int($val))
					{
						$field_opts[] = "'$option' => $val";
					}
					else
					{
						$field_opts[] = "'$option' => '$val'";
					}
				}
			}
			$field_opts = implode(', ', $field_opts);
			
			$field_up_str .= "\t\t\t'$name' => array({$field_opts}),".PHP_EOL;
			$field_down[] = "'$name'";
		}
    $field_down_str = implode(',', $field_down);
		$up = <<<UP
    \DBUtil::add_fields('{$subjects[1]}', array(
\t\t\t$field_up_str
    ));	
UP;
    $down = <<<DOWN
    \DBUtil::drop_fields('{$subjects[1]}', array(
\t\t\t$field_down_str    
    ));
DOWN;
    return array($up, $down);
	}
	
	// rename_field_{table}_{fieldname}_to_{newfieldname}
	public static function rename_field($subjects, $fields)
	{
		$column_list = \DB::list_columns($subjects[0], $subjects[1]);
		$column = $column_list[$subjects[1]];

		switch ($column['type'])
		{
			case 'float':
				$constraint = '\''.$column['numeric_precision'].', '.$column['numeric_scale'].'\'';
			break;
			case 'int':
				$constraint = $column['display'];
			break;
			case 'string':
				switch ($column['data_type'])
				{
					case 'binary':
					case 'varbinary':
					case 'char':
					case 'varchar':
            $constraint = $column['character_maximum_length'];
					break;

					case 'enum':
					case 'set':
					  $constraint = '"\''.implode('\',\'',$column['options']).'\'"';
					break;
				}
			break;
		}
		$constraint_str = isset($constraint) ? ", 'constraint' => $constraint" : '';
		$up = <<<UP
		\DBUtil::modify_fields('{$subjects[0]}', array(
\t\t\t'{$subjects[1]}' => array('name' => '{$subjects[2]}', 'type' => '{$column['data_type']}'$constraint_str)
		));
UP;
    $down = <<<DOWN
    \DBUtil::modify_fields('{$subjects[0]}', array(
\t\t\t'{$subjects[2]}' => array('name' => '{$subjects[1]}', 'type' => '{$column['data_type']}'$constraint_str)
		));
DOWN;
    return array($up, $down);
	}
	
	// rename_table_{tablename}_to_{newtablename}
	public static function rename_table($subjects, $fields)
	{
		
		$up = <<<UP
		\DBUtil::rename_table('{$subjects[0]}', '{$subjects[1]}');
UP;
		$down = <<<DOWN
		\DBUtil::rename_table('{$subjects[1]}', '{$subjects[0]}');
DOWN;
		
		return array($up, $down);
	}
	
	// drop_{tablename}
	public static function drop($subjects, $fields)
	{	
		$up = <<<UP
		\DBUtil::drop_table('{$subjects[1]}');
UP;

		// TODO Create down by looking at the table and building a create

		return array($up, '');
	}
	
}