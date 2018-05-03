<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.1
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
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
 * @author		Tom Arnfeld, Harro Verton
 */
class Generate_Migration_Actions
{
	/*****************************************************************************************************
	 * Each migration action should return an array with two items, 0 being the up and 1 the being down. *
	 *****************************************************************************************************/

	/**
	 *	In the methods below, the subjects array contains two elements:
	 *
	 *	- In a migration named 'create_users' the subject is 'users' since thats what we want to create
	 *		So it would be the second object in the array
	 *			array(false, 'users')
	 *
	 *	- In a migration named 'add_name_to_users' the object is 'name' and the subject is 'users'.
	 *		So again 'users' would be the second object, but 'name' would be the first
	 *			array('name', 'users')
	 *
	 *	- In case there are multiple objects, the array can have more objects. The subject will always be
	 *		last. So in a migration name 'rename_fullname_to_lastname_in_users, the array would contain
	 * 			array('fullname', 'lastname', 'users')
	 */

	/**
	 * Generate the up and down migration code for table creation
	 *
	 * oil command: create_{tablename}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function create($subjects, $fields)
	{
		if (count($subjects) != 2)
		{
			throw new \FuelException('Incorrect number of arguments for "create"');
		}

		// temp storage
		$table_prefix = '';

		// if we didn't get generated data, take the prefix into account
		if ( ! \Cli::option('no-standardisation'))
		{
			$active_db = \Config::get('db.active');
			$table_prefix = \Config::get('db.'.$active_db.'.table_prefix');
		}

		// generate the code for the fields
		list($field_up_str, $not_used, $pks, $idx) = static::_generate_field_string($fields);

		// construct the primary key list
		$pk_str = '';
		if ($pks)
		{
			$pk_str = array();
			foreach ($pks as $pk)
			{
				$pk_str[$pk['order']] = $pk['column'];
			}
			ksort($pk_str);
			$pk_str = ", array('".implode("', '", $pk_str)."')";
		}

		// generate the up() code
		$up = <<<UP
		\DBUtil::create_table('{$table_prefix}{$subjects[1]}', array(
$field_up_str
		)$pk_str);
UP;

		// generate the down() code
		$down = '';
		if ($idx)
		{
			// transform the index data
			$tidx = array();
			foreach ($idx as $idxval)
			{
				if ( ! isset($tidx[$idxval['name']]))
				{
					$tidx[$idxval['name']] = array((int)$idxval['order'] => $idxval);
				}
				else
				{
					$tidx[$idxval['name']][(int)$idxval['order']] = $idxval;
				}
			}

			$up .= PHP_EOL;
			foreach ($tidx as $name => $idx)
			{
				$field = array();
				foreach ($idx as $fidx)
				{
					$fidx['column'] = \DB::quote_identifier($fidx['column']);
					if ( ! $fidx['ascending'])
					{
						$fidx['column'] .= ' DESC';
					}
					$field[] = $fidx['column'];
				}
				$unique = reset($idx);
				$unique = $unique['unique'] ? ' UNIQUE' : '';
				$field = implode(', ', $field);
				$up .= PHP_EOL."\t\t\\DB::query('CREATE{$unique} INDEX {$name} ON {$table_prefix}{$subjects[1]}({$field})')->execute();";
				$down .= PHP_EOL."\t\t\\DB::query('DROP INDEX {$name} ON {$table_prefix}{$subjects[1]}')->execute();";
			}
			$down = ltrim($down, PHP_EOL).PHP_EOL.PHP_EOL;
		}

		$down .= <<<DOWN
		\DBUtil::drop_table('{$table_prefix}{$subjects[1]}');
DOWN;

		return array($up, $down);
	}

	/**
	 * Generate the up and down migration code for table deletion
	 *
	 * oil command: drop_{tablename}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function drop($subjects, $fields)
	{
		if (count($subjects) != 2)
		{
			throw new \FuelException('Incorrect number of arguments for "drop"');
		}

		// make sure the table we're about to drop exists
		if ( ! \DBUtil::table_exists($subjects[1]))
		{
			throw new \FuelException('Can not generate the migration. The table "'.$subjects[1].'" does not exist');
		}

		// in case of drop, we don't have field data, so fetch that first
		$fields = Generate::normalize_args(\DB::list_columns($subjects[1]));

		// same commands as for create
		$result = static::create($subjects, $fields);

		// but then in reverse order
		return array($result[1], $result[0]);
	}

	/**
	 * Generate the up and down migration code for adding a field to a table
	 *
	 * oil command: add_{thing}_to_{tablename}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function add($subjects, $fields)
	{
		if (count($subjects) != 2)
		{
			throw new \FuelException('Incorrect number of arguments for "add"');
		}

		// temp storage
		$table_prefix = '';

		// if we didn't get generated data, take the prefix into account
		if ( ! \Cli::option('no-standardisation'))
		{
			$active_db = \Config::get('db.active');
			$table_prefix = \Config::get('db.'.$active_db.'.table_prefix');
		}

		// generate the code for the fields
		list($field_up_str, $field_down_str, $not_used, $not_used) = static::_generate_field_string($fields);

		$up = <<<UP
		\DBUtil::add_fields('{$subjects[1]}', array(
$field_up_str
		));
UP;
		$down = <<<DOWN
		\DBUtil::drop_fields('{$subjects[1]}', array(
$field_down_str
		));
DOWN;
		return array($up, $down);
	}

	/**
	 * Generate the up and down migration code for deleting a field from a table
	 *
	 * oil command: delete_{thing}_from_{tablename}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function delete($subjects, $fields)
	{
		if (count($subjects) != 2)
		{
			throw new \FuelException('Incorrect number of arguments for "delete"');
		}

		// same commands as for add
		$result = static::add($subjects, $fields);

		// but then in reverse order
		return array($result[1], $result[0]);
	}

	/**
	 * Generate the up and down migration code for deleting a field from a table
	 *
	 * oil command: rename_field_{fieldname}_to_{newfieldname}_in_{table}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function rename_field($subjects, $fields)
	{
		if (count($subjects) != 3)
		{
			throw new \FuelException('Incorrect number of arguments for "rename_field"');
		}

		// make sure the table we're about to rename a field in exists
		$table = end($subjects);
		if ( ! \DBUtil::table_exists($table))
		{
			throw new \FuelException('Can not generate the migration. The table "'.$table.'" does not exist');
		}

		// make sure the old field exists, and the new field doesn't
		$column = \DB::list_columns($table, $subjects[1]);
		if ( ! empty($column))
		{
			throw new \FuelException('Can not generate the migration. The field "'.$subjects[1].'" already exists in "'.$table.'"');
		}
		$column = \DB::list_columns($table, $subjects[0]);
		if (empty($column))
		{
			throw new \FuelException('Can not generate the migration. The field "'.$subjects[0].'" does not exist in "'.$table.'"');
		}

		// generate the code for the fields
		list($field_up_str, $field_down_str, $not_used, $not_used) = static::_generate_field_string($column);

		// modify for different dbutil syntax
		$field_down_str = str_replace('array(', 'array(\'name\' => \''.$subjects[0].'\', ', str_replace($subjects[0], $subjects[1], $field_up_str));
		$field_up_str = str_replace('array(', 'array(\'name\' => \''.$subjects[1].'\', ', $field_up_str);

		$up = <<<UP
		\DBUtil::modify_fields('{$table}', array(
$field_up_str
		));
UP;
		$down = <<<DOWN
	\DBUtil::modify_fields('{$table}', array(
$field_down_str
		));
DOWN;
		return array($up, $down);
	}

	/**
	 * Generate the up and down migration code for deleting a field from a table
	 *
	 * oil command: rename_table_{tablename}_to_{newtablename}
	 *
	 * @param  array
	 * @param  array
	 *
	 * @return array(up, down)
	 */
	public static function rename_table($subjects, $fields)
	{
		if (count($subjects) != 2)
		{
			throw new \FuelException('Incorrect number of arguments for "rename_table"');
		}

		$up = <<<UP
		\DBUtil::rename_table('{$subjects[0]}', '{$subjects[1]}');
UP;

		$down = <<<DOWN
		\DBUtil::rename_table('{$subjects[1]}', '{$subjects[0]}');
DOWN;

		return array($up, $down);
	}

	// helpers

	/**
	 * generate the field definitions for up, down, and indexes
	 */
	protected static function _generate_field_string($fields)
	{
		// temp vars
		$idx = array();
		$pks = array();
		$field_up_str = '';
		$fields_down = array();

		// loop over the fields
		foreach($fields as $name => $field)
		{
			// storage for the translated field options
			$field_opts = array();

			// loop over the field options
			foreach($field as $option => $val)
			{
				// deal with index data first
				if ($option == 'indexes')
				{
					foreach ($val as $validx)
					{
						// deal with primary indexes
						if ($validx['primary'])
						{
							$pks[] = $validx;
						}

						// secondary index
						else
						{
							$idx[] = $validx;
						}
					}
					continue;
				}

				// skip option data from describe not supported by DBUtil::create_table()
				if (in_array($option, array('indexes', 'key', 'max', 'min', 'name', 'type', 'ordinal_position', 'display', 'comment', 'privileges', 'collation_name', 'options', 'character_maximum_length', 'numeric_precision', 'numeric_scale', 'exact')))
				{
					continue;
				}

				// skip empty constraints
				if ($option == 'constraint' and empty($val))
				{
					continue;
				}

				// rename options if need be
				if ($option == 'data_type')
				{
					$option = 'type';
				}
				if ($option == 'extra')
				{
					if ($val != 'auto_increment')
					{
						continue;
					}
					$option = 'auto_increment';
					$val = true;
				}


				// create the options based on the value type
				if ($val === true)
				{
					$field_opts[] = "'$option' => true";
				}
				elseif ($val === false)
				{
					$field_opts[] = "'$option' => false";
				}
				elseif (is_null($val))
				{
					// skip value
				}
				elseif (is_int($val))
				{
					$field_opts[] = "'$option' => $val";
				}
				elseif (is_array($val))
				{
					// skip value
				}
				else
				{
					$field_opts[] = "'$option' => '$val'";
				}
			}
			$field_opts = implode(', ', $field_opts);

			$field_up_str .= "\t\t\t'$name' => array({$field_opts}),".PHP_EOL;
			$fields_down[] = "\t\t\t'$name'".PHP_EOL;
		}

		$field_up_str = rtrim($field_up_str, PHP_EOL);
		$field_down_str = rtrim(implode(',', $fields_down), PHP_EOL);

		return array($field_up_str, $field_down_str, $pks, $idx);
	}
}
