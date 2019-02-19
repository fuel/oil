<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2018 Fuel Development Team
 * @link       https://fuelphp.com
 */

namespace Oil;

/**
 * Oil\Generate Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Generate
{
	public static $create_folders = array();
	public static $create_files = array();

	public static $scaffolding = false;

	protected static $_field_defaults = array(
		'_default_' => array(
			'null' => false,
			'key' => NULL,
		),
		'varchar' => array(
			'constraint' => '255',
		),
		'char' => array(
			'constraint' => '255',
		),
		'int' => array(
			'constraint' => '11',
		),
		'decimal' => array(
			'constraint' => '10,2',
		),
		'float' => array(
			'constraint' => '10,2',
		),
	);

	/**
	 *
	 */
	public static function config($args)
	{
		$file = strtolower(array_shift($args));

		if (empty($file))
		{
			throw new Exception('No config filename has been provided.');
		}

		$config = array();

		// load the config
		if ($paths = \Finder::search('config', $file, '.php', true))
		{
			// Reverse the file list so that we load the core configs first and
			// the app can override anything.
			$paths = array_reverse($paths);
			foreach ($paths as $path)
			{
				$config = \Fuel::load($path) + $config;
			}
		}
		unset($path);

		// We always pass in fields to a config, so lets sort them out here.
		foreach ($args as $conf)
		{
			// Each paramater for a config is seperated by the : character
			$parts = explode(":", $conf);

			// We must have the 'name:value' if nothing else!
			if (count($parts) >= 2)
			{
				$config[$parts[0]] = $parts[1];
			}
		}

		$overwrite = (\Cli::option('o') or \Cli::option('overwrite'));

		// strip whitespace and add tab
		$export = str_replace(array('  ', 'array ('), array("\t", 'array('), var_export($config, true));

		$content = '<?php'.PHP_EOL.PHP_EOL.'return '.$export.';';
		$content .= <<<CONF


/* End of file $file.php */
CONF;

		$module = \Cli::option('module', \Cli::option('m'));

		// add support for `php oil g config module::file arg1:value1`
		if (strpos($file, '::') !== false)
		{
			list($module, $file) = explode('::', $file);
		}

		// get the namespace path (if available)
		if ( ! empty($module) and $path = \Autoloader::namespace_path('\\'.ucfirst($module)))
		{
			// strip the classes directory as we need the module root
			// and construct the filename
			$path = substr($path, 0, -8).'config'.DS.$file.'.php';
			$path_name = "\\".ucfirst($module).'::';
		}
		elseif ( ! empty($module))
		{
			throw new Exception("{$module} need to be loaded first, please use config always_load.modules.");
		}
		else
		{
			$path = APPPATH.'config'.DS.$file.'.php';
			$path_name = 'APPPATH/';
		}

		if ( ! $overwrite and is_file($path))
		{
			throw new Exception("{$path_name}/config/{$file}.php already exist, please use --overwrite option to force update");
		}

		$path = pathinfo($path);

		try
		{
			\File::update($path['dirname'], $path['basename'], $content);
			\Cli::write("Created config: {$path_name}config/{$file}.php", 'green');
		}
		catch (\InvalidPathException $e)
		{
			throw new Exception("Invalid basepath, cannot update at ".$path_name."config".DS."{$file}.php");
		}
		catch (\FileAccessException $e)
		{
			throw new Exception($path_name."config".DS.$file.".php could not be written.");
		}
	}

	/**
	 *
	 */
	public static function controller($args, $build = true)
	{
		if ( ! ($name = \Str::lower(array_shift($args))))
		{
			throw new Exception('No controller name was provided.');
		}

		// Do we want a view or a presenter?
		$with_presenter = \Cli::option('with-presenter') or \Cli::option('with-viewmodel');

 		$actions = $args;

		$filename = trim(str_replace(array('_', '-'), DS, $name), DS);

		$base_path = APPPATH;

		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}
		}

		$filepath = $base_path.'classes'.DS.'controller'.DS.$filename.'.php';

		// Uppercase each part of the class name and remove hyphens
		$class_name = \Inflector::classify(str_replace(array('\\', '/'), '_', $name), false);

		// Generate with test?
		$with_test = \Cli::option('with-test');
		if ($with_test) {
			static::_create_test('Controller', $class_name, $base_path);
		}

		// Stick "blog" to the start of the array
		array_unshift($args, $filename);

		// Create views folder and each view file
		if (\Cli::option('crud'))
		{
			static::views($args, 'scaffolding'.DS.'crud'.DS.'views', false);
		}
		else
		{
			static::views($args, 'scaffolding'.DS.'orm'.DS.'views', false);
		}

		$actions or $actions = array('index');

		$action_str = '';
		foreach ($actions as $action)
		{
			$action_str .= '
	public function action_'.$action.'()
	{
		$data["subnav"] = array(\''.$action.'\'=> \'active\' );
		$this->template->title = \'' . \Inflector::humanize($name) .' &raquo; ' . \Inflector::humanize($action) . '\';
		$this->template->content = View::forge(\''.$filename.'/' . $action .'\', $data);
	}'.PHP_EOL;
		}

		$extends = \Cli::option('extends', 'Controller_Template');
		$prefix = \Config::get('controller_prefix', 'Controller_');

		// Build Controller
		$controller = <<<CONTROLLER
<?php

class {$prefix}{$class_name} extends {$extends}
{
{$action_str}
}

CONTROLLER;

		// Write controller
		static::create($filepath, $controller, 'controller');

		// Do you want a presenter with that?
		if ($with_presenter)
		{
			$presenter_filepath = $base_path.'classes'.DS.'presenter'.DS.$filename;

			// One Presenter per action
			foreach ($actions as $action)
			{
				$presenter = <<<PRESENTER
<?php

class Presenter_{$class_name}_{$action} extends Presenter
{
	public function view()
	{
		\$this->content = "{$class_name} &raquo; {$action}";
	}
}
PRESENTER;

				// Write presenter
				static::create($presenter_filepath.DS.$action.'.php', $presenter, 'presenter');
			}
		}

		// Generate with test?
		$with_test = \Cli::option('with-test');
		if ($with_presenter and $with_test) {
			static::_create_test('Presenter', $class_name, $base_path);
		}

		$build and static::build();
	}

	/**
	 *
	 */
	public static function model($args, $build = true)
	{
		$singular = \Inflector::singularize(\Str::lower(array_shift($args)));

		$args = static::normalize_args($args);

		if (empty($singular) or strpos($singular, ':'))
		{
			throw new Exception("Command is invalid.".PHP_EOL."\tphp oil g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]");
		}

		if (empty($args))
		{
			throw new Exception('No fields have been provided, the model will not know how to build the table.');
		}

		$plural = (\Cli::option('singular') or \Cli::option('no-standardisation')) ? $singular : \Inflector::pluralize($singular);

		$filename = trim(str_replace(array('_', '-'), DS, $singular), DS);
		$base_path = APPPATH;

		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}

			$module_namespace = ucwords($module);
		}

		$filepath = $base_path.'classes'.DS.'model'.DS.$filename.'.php';

		// Uppercase each part of the class name and remove hyphens
		$class_name = \Inflector::classify(str_replace(array('\\', '/'), '_', $singular), false);

		// Generate with test?
		if ($with_test = \Cli::option('with-test'))
		{
			static::_create_test('Model', $class_name, $base_path);
		}

		// storage for the generated contents
		$contents = '';

		// deal with Model_Crud models first
		if (\Cli::option('crud'))
		{
			// model properties
			if ( ! \Cli::option('no-properties'))
			{
				$contents = <<<CONTENTS
	protected static \$_properties = array(
CONTENTS;
				foreach ($args as $arg)
				{
					$contents .= PHP_EOL."\t\t\"".$arg['name']."\",";
				}
				$contents .= <<<CONTENTS

	);

CONTENTS;
			}

			// created-at field
			if($created_at = \Cli::option('created-at'))
			{
				is_string($created_at) or $created_at = 'created_at';

				$contents .= <<<CONTENTS

	protected static \$_created_at = '$created_at';

CONTENTS;
			}

			// updated-at field
			if($updated_at = \Cli::option('updated-at'))
			{
				is_string($updated_at) or $updated_at = 'updated_at';

				$contents .= <<<CONTENTS

	protected static \$_updated_at = '$updated_at';

CONTENTS;
			}

			// mysql-timestamp field
			if(\Cli::option('mysql-timestamp'))
			{
				$contents .= <<<CONTENTS

	protected static \$_mysql_timestamp = true;

CONTENTS;
			}

			// table name used by this model
			$contents .= <<<CONTENTS

	protected static \$_table_name = '{$plural}';

CONTENTS;

			// namespace and class definition
			if ($module)
			{
				$model = <<<MODEL
<?php namespace {$module_namespace};

class Model_{$class_name} extends \Model_Crud
{
{$contents}
}

MODEL;
			}
			else
			{
				$model = <<<MODEL
<?php

class Model_{$class_name} extends \Model_Crud
{
{$contents}
}

MODEL;
			}
		}

		// ORM models
		else
		{

			// model properties
			if ( ! \Cli::option('no-properties'))
			{
				$contents = <<<CONTENTS
	protected static \$_properties = array(
CONTENTS;
				foreach ($args as $arg)
				{
					$contents .= PHP_EOL."\t\t\"".$arg['name']."\" => array(";
					$contents .= PHP_EOL."\t\t\t\"label\" => \"".\Inflector::humanize($arg['name'])."\",";
					$contents .= PHP_EOL."\t\t\t\"data_type\" => \"".$arg['data_type']."\",";
					if (isset($arg['default']))
					{
						$contents .= PHP_EOL."\t\t\t\"default\" => \"".$arg['default']."\",";
					}
					if ($arg['data_type'] == 'enum' and isset($arg['options']))
					{
						$contents .= PHP_EOL."\t\t\t\"options\" => array(".$arg['constraint']."),";
					}

					$contents .= PHP_EOL."\t\t),";
				}
				$contents .= <<<CONTENTS

	);

CONTENTS;
			}

			// determine the type of timestamp used
			$mysql_timestamp = (\Cli::option('mysql-timestamp')) ? 'true' : 'false';

			// add date observers if needed
			$contents .= <<<CONTENTS

	protected static \$_observers = array(
CONTENTS;
			if ( ! \Cli::option('no-timestamp') and ! \Cli::option('no-standardisation'))
			{
				$created_at = \Cli::option('created-at', 'created_at');
				is_string($created_at) or $created_at = 'created_at';

				$updated_at = \Cli::option('updated-at', 'updated_at');
				is_string($updated_at) or $updated_at = 'updated_at';

				$contents .= <<<CONTENTS

		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'property' => '$created_at',
			'mysql_timestamp' => $mysql_timestamp,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_update'),
			'property' => '$updated_at',
			'mysql_timestamp' => $mysql_timestamp,
		),
CONTENTS;
			}

			$contents .= <<<CONTENTS

	);

CONTENTS;

			// add fields required for soft-delete models
			if (\Cli::option('soft-delete'))
			{
				$deleted_at = \Cli::option('deleted_at', 'deleted_at');
				is_string($deleted_at) or $deleted_at = 'deleted_at';

				$contents .= <<<CONTENTS

	protected static \$_soft_delete = array(
		'mysql_timestamp' => $mysql_timestamp,
		'deleted_field' => '{$deleted_at}',
	);

CONTENTS;
			}

			// add fields required for temporal models
			elseif (\Cli::option('temporal'))
			{
				$temporal_start = \Cli::option('temporal-start', 'temporal_start');
				is_string($temporal_start) or $temporal_start = 'temporal_start';

				$temporal_end = \Cli::option('temporal-end', 'temporal_end');
				is_string($temporal_end) or $temporal_end = 'temporal_end';

				$contents .= <<<CONTENTS


	protected static \$_temporal = array(
		'mysql_timestamp' => $mysql_timestamp,
		'start_column' => '{$temporal_start}',
		'end_column' => '{$temporal_end}',

	);
CONTENTS;
			}

			// add fields required for nestedset models
			elseif (\Cli::option('nestedset'))
			{
				$contents .= <<<CONTENTS

	protected static \$_tree = array(

CONTENTS;

				if ($title = \Cli::option('title', false))
				{
					is_string($title) or $title = 'title';
					$contents .= <<<CONTENTS
		'title_field' => '{$title}',

CONTENTS;
				}

				if ($tree_id = \Cli::option('tree-id', false))
				{
					is_string($tree_id) or $tree_id = 'tree_id';
					$contents .= <<<CONTENTS
		'tree_field' => '{$tree_id}',

CONTENTS;
				}

				$left_id = \Cli::option('left-id', 'left_id');
				is_string($left_id) or $left_id = 'left_id';
				$contents .= <<<CONTENTS
		'left_field' => '{$left_id}',

CONTENTS;


				$right_id = \Cli::option('right-id', 'right_id');
				is_string($right_id) or $right_id = 'right_id';
				$contents .= <<<CONTENTS
		'right_field' => '{$right_id}',

CONTENTS;

				if($read_only = \Cli::option('read-only') and is_string($read_only))
				{
					$read_only = explode(',', $read_only);
					$read_only = "'" . implode("', '", $read_only) . "'";
					$read_only = <<<CONTENTS
		'read_only' => array($read_only),

CONTENTS;
				}

				$contents .= <<<CONTENTS
	);

CONTENTS;

			}

			// database table name
			$contents .= <<<CONTENTS

	protected static \$_table_name = '{$plural}';

CONTENTS;

			// primary keys
			$keys = array();
			foreach($args as $arg)
			{
				if (isset($arg['indexes']))
				{
					foreach ($arg['indexes'] as $idx)
					{
						if ($idx['primary'] === true)
						{
							$keys[$idx['order']] = $idx['column'];
						}
					}
				}
			}

			// define the primary keys
			$contents .= <<<CONTENTS

	protected static \$_primary_key = array('
CONTENTS;
			$contents .= implode("', '", $keys);
			$contents .= <<<CONTENTS
');

CONTENTS;

			// add empty relation structures
			$contents .= <<<CONTENTS

	protected static \$_has_many = array(
	);

	protected static \$_many_many = array(
	);

	protected static \$_has_one = array(
	);

	protected static \$_belongs_to = array(
	);

CONTENTS;

			// define the ORM class
			$model = '';
			if ( \Cli::option('soft-delete'))
			{
				if ($module)
				{
					$model .= <<<MODEL
<?php namespace {$module_namespace};

class Model_{$class_name} extends \Orm\Model_Soft
{
{$contents}
}

MODEL;
				}
				else
				{
					$model .= <<<MODEL
<?php

class Model_{$class_name} extends \Orm\Model_Soft
{
{$contents}
}

MODEL;
				}
			}
			elseif ( \Cli::option('temporal'))
			{
				$model .= <<<MODEL
<?php

class Model_{$class_name} extends \Orm\Model_Temporal
{
{$contents}
}

MODEL;
			}
			elseif ( \Cli::option('nestedset'))
			{
				$model .= <<<MODEL
<?php

class Model_{$class_name} extends \Orm\Model_Nestedset
{
{$contents}
}

MODEL;
			}
			else
			{
				if ($module)
				{
					$model .= <<<MODEL
<?php namespace {$module_namespace};

class Model_{$class_name} extends \Orm\Model
{
{$contents}
}

MODEL;
				}
				else
				{
					$model .= <<<MODEL
<?php

class Model_{$class_name} extends \Orm\Model
{
{$contents}
}

MODEL;
				}
			}
		}

		// Build the model
		static::create($filepath, $model, 'model');

		if ( ! \Cli::option('no-migration'))
		{
			if ( ! empty($args))
			{
				array_unshift($args, 'create_'.$plural);
				static::migration($args, false);
			}

			else
			{
				throw new \Exception('Not enough arguments to create this migration.');
			}
		}

		$build and static::build();
	}

	/**
	 *
	 */
	public static function module($args)
	{
		if ( ! ($module_name = strtolower(array_shift($args)) ) )
		{
			throw new Exception('No module name has been provided.');
		}

		if ($path = \Module::exists($module_name))
		{
			throw new Exception('A module named '.$module_name.' already exists at '.$path);
		}

		$module_paths = \Config::get('module_paths');
		$base = reset($module_paths);

		if (count($module_paths) > 1)
		{
			\Cli::write('Your app has multiple module paths defined. Please choose the appropriate path from the list below', 'yellow', 'blue');

			$options = array();
			foreach ($module_paths as $key => $path)
			{
				$idx = $key+1;
				\Cli::write('['.$idx.'] '.$path);
				$options[] = $idx;
			}

			$path_idx = \Cli::prompt('Please choose the desired module path', $options);

			$base = $module_paths[$path_idx - 1];
		}

		$module_path = $base.$module_name.DS;

		static::$create_folders[] = $module_path;
		static::$create_folders[] = $module_path.'classes/';

		if ( ($folders = \Cli::option('folders')) !== true )
		{
			$folders = explode(',', $folders);

			foreach ($folders as $folder)
			{
				static::$create_folders[] = $module_path.$folder;
			}
		}

		static::$create_folders && static::build();
	}

	/**
	 *
	 */
	public static function views($args, $subfolder, $build = true)
	{
		$controller = strtolower(array_shift($args));
		$controller_title = \Inflector::humanize($controller);

		$base_path = APPPATH;
		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}
		}

		$view_dir = $base_path.'views/'.trim(str_replace(array('_', '-'), DS, $controller), DS).DS;

		$args or $args = array('index');

		// Make the directory for these views to be store in
		is_dir($view_dir) or static::$create_folders[] = $view_dir;

		// Add the default template if it doesn't exist
		if ( ! is_file($app_template = $base_path.'views/template.php') )
		{
			static::create($app_template, file_get_contents(\Package::exists('oil').'views/scaffolding/template.php'), 'view');
		}

		$subnav = '';
		foreach($args as $nav_item)
		{
			$subnav .= "\t<li class='<?php echo Arr::get(\$subnav, \"{$nav_item}\" ); ?>'><?php echo Html::anchor('{$controller}/{$nav_item}','".\Inflector::humanize($nav_item)."');?></li>".PHP_EOL;
		}

		foreach ($args as $action)
		{
			$view_title = (\Cli::option('with-presenter') or \Cli::option('with-viewmodel')) ? '<?php echo $content; ?>' : \Inflector::humanize($action);

			$view = <<<VIEW
<ul class="nav nav-pills">
{$subnav}
</ul>
<p>{$view_title}</p>
VIEW;

			// Generate with test?
 			$with_test = \Cli::option('with-test');
            		if ($with_test) {
               			static::_create_test('View', $controller, $base_path, $nav_item);
			}

			// Create this view
			static::create($view_dir.$action.'.php', $view, 'view');
		}

		$build and static::build();
	}

	/**
	 *
	 */
	public static function migration($args, $build = true)
	{
		// Get the migration name
		$migration_name = \Str::lower(str_replace(array('-', '/'), '_', array_shift($args)));

		// what type of migration do we have?
		$type = explode('_', $migration_name);

		// tell the generator not to standardize the fieldlist for these types
		if (in_array($type[0], array('add', 'delete', 'rename', 'drop')))
		{
			\Cli::set_option('no-standardisation', true);
		}

		// normalize the arguments if needed
		if ( ! empty($args))
		{
			$args = static::normalize_args($args);
		}

		if (empty($migration_name) or strpos($migration_name, ':'))
		{
			throw new Exception("Command is invalid.".PHP_EOL."\tphp oil g migration <migrationname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]");
		}

		$base_path = APPPATH;

		// Check if a migration with this name already exists
		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}
		}

		$duplicates = array();
		foreach($migrations = new \GlobIterator($base_path.'migrations/*_'.$migration_name.'*') as $migration)
		{
			// check if it's really a duplicate
			$part = explode('_', basename($migration->getFilename(), '.php'), 2);
			if ($part[1] != $migration_name)
			{
				$part = substr($part[1], strlen($migration_name)+1);
				if ( ! is_numeric($part))
				{
					// not a numbered suffix, but the same base classname
					continue;
				}
			}

			$duplicates[] = $migration->getPathname();
		}

		// save the migration name, it's also used as table name
		$table_name = $migration_name;

		// deal with duplicates to make sure the migration name is unique
		if (count($duplicates) > 0)
		{
			// Don't override a file
			if (\Cli::option('s', \Cli::option('skip')) === true)
			{
				return;
			}

			// Tear up the file path and name to get the last duplicate
			$file_name = pathinfo(end($duplicates), PATHINFO_FILENAME);

			// Override the (most recent) migration with the same name by using its number
			if (\Cli::option('f', \Cli::option('force')) === true)
			{
				list($number) = explode('_', $file_name);
			}

			// Name clashes but this is done by hand. Assume they know what they're doing and just increment the file
			elseif (static::$scaffolding === false)
			{
				// Increment the name of this
				$migration_name = \Str::increment(substr($file_name, 4), 2);
			}
		}

		// See if the action exists
		$methods = get_class_methods(__NAMESPACE__ . '\Generate_Migration_Actions');

		// For empty migrations that dont have actions
		$migration = array('', '');

		// Loop through the actions and act on a matching action appropriately
		foreach ($methods as $method_name)
		{
			// If the miration name starts with the name of the action method
			if (substr($table_name, 0, strlen($method_name)) === $method_name)
			{
				/**
				 *	Create an array of the subject the migration is about
				 *
				 *	- In a migration named 'create_users' the subject is 'users' since thats what we want to create
				 *		So it would be the second object in the array
				 *			array(false, 'users')
				 *
				 *	- In a migration named 'add_name_to_users' the object is 'name' and the subject is 'users'.
				 *		So again 'users' would be the second object, but 'name' would be the first
				 *			array('name', 'users')
				 *
				 */
				$subjects = array(false, false);
				$matches = explode('_', str_replace($method_name . '_', '', $table_name));

				// create_{table}
				if (count($matches) == 1)
				{
					$subjects = array(false, $matches[0]);
				}

				// add_{field}_to_{table}
				elseif (count($matches) == 3 && $matches[1] == 'to')
				{
					$subjects = array($matches[0], $matches[2]);
				}

				// delete_{field}_from_{table}
				elseif (count($matches) == 3 && $matches[1] == 'from')
				{
					$subjects = array($matches[0], $matches[2]);
				}

				// rename_field_{field}_to_{field}_in_{table} (with underscores in field names)
				elseif (count($matches) >= 5 && in_array('to', $matches) && in_array('in', $matches))
				{
					$subjects = array(
					 implode('_', array_slice($matches, 0, array_search('to', $matches))),
					 implode('_', array_slice($matches, array_search('to', $matches)+1, array_search('in', $matches)-array_search('to', $matches)-1)),
					 implode('_', array_slice($matches, array_search('in', $matches)+1)),
				  );
				}

				// rename_table
				elseif ($method_name == 'rename_table')
				{
					$subjects = array(
					 implode('_', array_slice($matches, 0, array_search('to', $matches))),
					 implode('_', array_slice($matches, array_search('to', $matches)+1)),
				  );
				}

				// create_{table} or drop_{table} (with underscores in table name)
				elseif (count($matches) !== 0)
				{
					$name = str_replace(array('create_', 'add_', 'drop_', '_to_'), array('create-', 'add-', 'drop-', '-to-'), $table_name);

    				if (preg_match('/^(create|drop|add)\-([a-z0-9\_]*)(\-to\-)?([a-z0-9\_]*)?$/i', $name, $deep_matches))
    				{
    					switch ($deep_matches[1])
    					{
    						case 'create' :
    						case 'drop' :
    							$subjects = array(false, $deep_matches[2]);
    						break;

    						case 'add' :
    							$subjects = array($deep_matches[2], $deep_matches[4]);
    						break;
    					}
    				}
				}

				// There is no subject here so just carry on with a normal empty migration
				else
				{
					break;
				}

				// Call the magic action which returns an array($up, $down) for the migration
				$migration = call_user_func(__NAMESPACE__ . "\Generate_Migration_Actions::{$method_name}", $subjects, $args);
			}
		}

		// Build the migration
		list($up, $down) = $migration;

		// If we don't have any, bail out
		if (empty($up) and empty($down))
		{
			throw new \Exception('No migration could be generated. Please verify your command syntax.');
			exit;
		}

		$migration_name = ucfirst(strtolower($migration_name));

		$migration = <<<MIGRATION
<?php

namespace Fuel\Migrations;

class {$migration_name}
{
	public function up()
	{
{$up}
	}

	public function down()
	{
{$down}
	}
}
MIGRATION;

		$number = isset($number) ? $number : static::_find_migration_number();
		$filepath = $base_path.'migrations/'.$number.'_'.strtolower($migration_name).'.php';

		static::create($filepath, $migration, 'migration');

		$build and static::build();
	}

	/**
	 *
	 */
	public static function task($args, $build = true)
	{

		if ( ! ($name = \Str::lower(array_shift($args))))
		{
			throw new Exception('No task name was provided.');
		}

		if (empty($args))
		{
			\Cli::write("\tNo tasks actions have been provided, the TASK will only create default task.", 'red');
		}

		$args or $args = array('index');

		// Uppercase each part of the class name and remove hyphens
		$class_name = \Inflector::classify($name, false);
		$filename = trim(str_replace(array('_', '-'), DS, $name), DS);

		$base_path = APPPATH;

		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}
		}

		$filepath = $base_path.'tasks'.DS.$filename.'.php';

		$action_str = '';

		foreach ($args as $action)
		{
			$task_path = '\\'.\Inflector::humanize($name).'\\'.\Inflector::humanize($action);

			if (!ctype_alpha($action[0])) {
				throw new Exception('An action does not start with alphabet character.  ABORTING');
			}

			$action_str .= '
	/**
	 * This method gets ran when a valid method name is not used in the command.
	 *
	 * Usage (from command line):
	 *
	 * php oil r '.$name.':'.$action.' "arguments"
	 *
	 * @return string
	 */
	public function '.$action.'($args = NULL)
	{
		echo "\n===========================================";
		echo "\nRunning task ['.\Inflector::humanize($name).':'. \Inflector::humanize($action) . ']";
		echo "\n-------------------------------------------\n\n";

		/***************************
		 Put in TASK DETAILS HERE
		 **************************/
	}'.PHP_EOL;

			$message = \Cli::color("\t\tPreparing task method [", 'green');
			$message .= \Cli::color(\Inflector::humanize($action), 'cyan');
			$message .= \Cli::color("]", 'green');
			\Cli::write($message);
		}

		// Default RUN task action
		$action = 'run';
		$default_action_str = '
	/**
	 * This method gets ran when a valid method name is not used in the command.
	 *
	 * Usage (from command line):
	 *
	 * php oil r '.$name.'
	 *
	 * @return string
	 */
	public function run($args = NULL)
	{
		echo "\n===========================================";
		echo "\nRunning DEFAULT task ['.\Inflector::humanize($name).':'. \Inflector::humanize($action) . ']";
		echo "\n-------------------------------------------\n\n";

		/***************************
		 Put in TASK DETAILS HERE
		 **************************/
	}'.PHP_EOL;

		// Build Controller
		$task_class = <<<CONTROLLER
<?php

namespace Fuel\Tasks;

class {$class_name}
{
{$default_action_str}

{$action_str}
}
/* End of file tasks/{$name}.php */

CONTROLLER;

		// Write controller
		static::create($filepath, $task_class, 'tasks');

		$build and static::build();
	}

	/**
	 *
	 */
	public static function help()
	{
		$output = <<<HELP
Usage:
  php oil [g|generate] [config|controller|views|model|migration|scaffold|admin|task|package] [options]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -q, [--quiet]    # Supress status output
  -t, [--speak]    # Speak errors in a robot voice

Description:
  The 'oil' command can be used to generate MVC components, database migrations
  and run specific tasks.

Examples:
  php oil generate controller <controllername> [<action1> |<action2> |..]
  php oil g model <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g migration <migrationname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g scaffold <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g scaffold/template_subfolder <modelname> [<fieldname1>:<type1> |<fieldname2>:<type2> |..]
  php oil g config <filename> [<key1>:<value1> |<key2>:<value2> |..]
  php oil g task <taskname> [<cmd1> |<cmd2> |..]
  php oil g package <packagename>

Note that the next two lines are equivalent:
  php oil g scaffold <modelname> ...
  php oil g scaffold/orm <modelname> ...

Documentation:
  https://docs.fuelphp.com/packages/oil/generate.html
HELP;

		\Cli::write($output);
	}

	/**
	 *
	 */
	public static function package($args, $build = true)
	{
		$name       = str_replace(array('/', '_', '-'), '', \Str::lower(array_shift($args)));
		$class_name = ucfirst($name);
		$vcs        = \Cli::option('vcs', \Cli::option('v', false));
		$path       = \Cli::option('path', \Cli::option('p', PKGPATH));
		$drivers    = \Cli::option('drivers', \Cli::option('d', ''));

		if (empty($name))
		{
			throw new \Exception('No package name has been provided.');
		}

		if ( ! in_array($path, \Config::get('package_paths')) and ! in_array(realpath($path), \Config::get('package_paths')) )
		{
			throw new \Exception('Given path is not a valid package path.');
		}

		\Str::ends_with($path, DS) or $path .= DS;
		$path .= $name . DS;

		if (is_dir($path))
		{
			throw new \Exception('Package already exists.');
		}

		if ($vcs)
		{
			$output = <<<COMPOSER
{
	"name": "fuel/{$name}",
	"type": "fuel-package",
	"description": "{$class_name} package",
	"keywords": [""],
	"homepage": "https://fuelphp.com",
	"license": "MIT",
	"authors": [
		{
			"name": "AUTHOR",
			"email": "AUTHOR@example.com"
		}
	],
	"require": {
		"composer/installers": "~1.0"
	},
	"extra": {
		"installer-name": "{$name}"
	}
}

COMPOSER;

			static::create($path . 'composer.json', $output);

			$output = <<<README
# {$class_name} package
Here comes some description

README;

			static::create($path . 'README.md', $output);
		}

		if ( ! empty($drivers))
		{
			$drivers === true or $drivers = explode(',', $drivers);

			$output = <<<CLASS
<?php

namespace {$class_name};

class {$class_name}Exception extends \FuelException {}

class {$class_name}
{
	/**
	 * loaded instance
	 */
	protected static \$_instance = null;

	/**
	 * array of loaded instances
	 */
	protected static \$_instances = array();

	/**
	 * Default config
	 * @var array
	 */
	protected static \$_defaults = array();

	/**
	 * Init
	 */
	public static function _init()
	{
		\Config::load('{$name}', true);
	}

	/**
	 * {$class_name} driver forge.
	 *
	 * @param	string			\$instance		Instance name
	 * @param	array			\$config		Extra config array
	 * @return  {$class_name} instance
	 */
	public static function forge(\$instance = 'default', \$config = array())
	{
		is_array(\$config) or \$config = array('driver' => \$config);

		\$config = \Arr::merge(static::\$_defaults, \Config::get('{$name}', array()), \$config);

		\$class = '\\{$class_name}\\{$class_name}_' . ucfirst(strtolower(\$config['driver']));

		if( ! class_exists(\$class, true))
		{
			throw new \FuelException('Could not find {$class_name} driver: ' . ucfirst(strtolower(\$config['driver'])));
		}

		\$driver = new \$class(\$config);

		static::\$_instances[\$instance] = \$driver;

		return \$driver;
	}

	/**
	 * Return a specific driver, or the default instance (is created if necessary)
	 *
	 * @param   string  \$instance
	 * @return  {$class_name} instance
	 */
	public static function instance(\$instance = null)
	{
		if (\$instance !== null)
		{
			if ( ! array_key_exists(\$instance, static::\$_instances))
			{
				return false;
			}

			return static::\$_instances[\$instance];
		}

		if (static::\$_instance === null)
		{
			static::\$_instance = static::forge();
		}

		return static::\$_instance;
	}
}

CLASS;

			static::create($path . 'classes' . DS . $name . '.php', $output);

			$output = <<<DRIVER
<?php

namespace {$class_name};

abstract class {$class_name}_Driver
{
	/**
	* Driver config
	* @var array
	*/
	protected \$config = array();

	/**
	* Driver constructor
	*
	* @param array \$config driver config
	*/
	public function __construct(array \$config = array())
	{
		\$this->config = \$config;
	}

	/**
	* Get a driver config setting.
	*
	* @param string \$key the config key
	* @param mixed  \$default the default value
	* @return mixed the config setting value
	*/
	public function get_config(\$key, \$default = null)
	{
		return \Arr::get(\$this->config, \$key, \$default);
	}

	/**
	* Set a driver config setting.
	*
	* @param string \$key the config key
	* @param mixed \$value the new config value
	* @return object \$this for chaining
	*/
	public function set_config(\$key, \$value)
	{
		\Arr::set(\$this->config, \$key, \$value);

		return \$this;
	}
}

DRIVER;

			static::create($path . 'classes' . DS . $name . DS . 'driver.php', $output);

			$bootstrap =  PHP_EOL."\t'{$class_name}\\\\{$class_name}_Driver' => __DIR__ . '/classes/{$name}/driver.php',";
			if (is_array($drivers))
			{
				foreach ($drivers as $driver)
				{
					$driver = \Str::lower($driver);
					$driver_name = ucfirst($driver);
					$output = <<<CLASS
<?php

namespace {$class_name};

class {$class_name}_{$driver_name}  extends {$class_name}_Driver
{
	/**
	* Driver specific functions
	*/
}

CLASS;
					$bootstrap .= PHP_EOL."\t'{$class_name}\\\\{$class_name}_{$driver_name}' => __DIR__ . '/classes/{$name}/{$driver}.php',";
					static::create($path . 'classes' . DS . $name . DS . $driver . '.php', $output);
				}
			}
		}
		else
		{
			$output = <<<CLASS
<?php

namespace {$class_name};

class {$class_name}Exception extends \FuelException {}

class {$class_name}
{
	/**
	 * Default config
	 * @var array
	 */
	protected static \$_defaults = array();

	/**
	* Driver config
	* @var array
	*/
	protected \$config = array();

	/**
	 * Init
	 */
	public static function _init()
	{
		\Config::load('{$name}', true);
	}

	/**
	 * {$class_name} driver forge.
	 *
	 * @param	array			\$config		Config array
	 * @return  {$class_name}
	 */
	public static function forge(\$config = array())
	{
		\$config = \Arr::merge(static::\$_defaults, \Config::get('{$name}', array()), \$config);

		\$class = new static(\$config);

		return \$class;
	}

	/**
	* Driver constructor
	*
	* @param array \$config driver config
	*/
	public function __construct(array \$config = array())
	{
		\$this->config = \$config;
	}

	/**
	* Get a config setting.
	*
	* @param string \$key the config key
	* @param mixed  \$default the default value
	* @return mixed the config setting value
	*/
	public function get_config(\$key, \$default = null)
	{
		return \Arr::get(\$this->config, \$key, \$default);
	}

	/**
	* Set a config setting.
	*
	* @param string \$key the config key
	* @param mixed \$value the new config value
	* @return object \$this for chaining
	*/
	public function set_config(\$key, \$value)
	{
		\Arr::set(\$this->config, \$key, \$value);

		return \$this;
	}
}

CLASS;

			static::create($path . 'classes' . DS . $name . '.php', $output);

			$bootstrap = "";
		}

			$output = <<<CONFIG
<?php

return array(

);

CONFIG;

			static::create($path . 'config' . DS . $name . '.php', $output);

		$output = <<<CLASS
<?php

Autoloader::add_core_namespace('{$class_name}');

Autoloader::add_classes(array(
	'{$class_name}\\\\{$class_name}' => __DIR__ . '/classes/{$name}.php',
	'{$class_name}\\\\{$class_name}Exception' => __DIR__ . '/classes/{$name}.php',
{$bootstrap}
));

CLASS;
		static::create($path . 'bootstrap.php', $output);

		$build and static::build();
	}

	/**
	 *
	 */
	public static function create($filepath, $contents, $type = 'file')
	{
		$directory = dirname($filepath);
		is_dir($directory) or static::$create_folders[] = $directory;

		// Check if a file exists then work out how to react
		if (is_file($filepath))
		{
			// Don't override a file
			if (\Cli::option('s', \Cli::option('skip')) === true)
			{
				// Don't bother trying to make this, carry on camping
				return;
			}

			// If we aren't skipping it, tell em to use -f
			if (\Cli::option('f', \Cli::option('force')) === null)
			{
				throw new Exception($filepath .' already exists, use -f or --force to override.');
				exit;
			}
		}

		static::$create_files[] = array(
			'path' => $filepath,
			'contents' => $contents,
			'type' => $type,
		);
	}

	/**
	 *
	 */
	public static function build()
	{
		foreach (static::$create_folders as $folder)
		{
			is_dir($folder) or mkdir($folder, 0755, TRUE);
		}

		$result = true;

		foreach (static::$create_files as $file)
		{
			\Cli::write("\tCreating {$file['type']}: {$file['path']}", 'green');

			if ( ! $handle = @fopen($file['path'], 'w+'))
			{
				throw new Exception('Cannot open file: '. $file['path']);
			}

			$result = @fwrite($handle, $file['contents']);

			// Write $somecontent to our opened file.
			if ($result === false)
			{
				throw new Exception('Cannot write to file: '. $file['path']);
			}

			@fclose($handle);

			@chmod($file['path'], 0666);
		}

		return $result;
	}

	/**
	 *
	 */
	public static function class_name($name)
	{
		return str_replace(array(' ', '-'), '_', ucwords(str_replace('_', ' ', $name)));
	}

	/**
	 *
	 */
	public static function normalize_args(array $args)
	{
		// normalized result
		$normalized = array('id' => null);

		// loop over the field names passed
		foreach ($args as $field)
		{
			// check what we got
			if (is_array($field))
			{
				// make sure we have the correct format
				if (isset($field['name']))
				{
					// deal with some generics
					if ($field['data_type'] === 'string')
					{
						$field['data_type'] = 'varchar';
					}
					elseif ($field['data_type'] === 'integer')
					{
						$field['data_type'] = 'int';
					}
					elseif (strpos($field['data_type'], ' unsigned') !== false)
					{
						$field['data_type'] = explode(' ', $field['data_type']);
						$field['data_type'] = $field['data_type'][0];
						$field['unsigned'] = true;
					}

					// deal with some constraint quirks
					if (empty($field['constraint']))
					{
						if (isset($field['display']))
						{
							$field['constraint'] = $field['display'];
						}
						elseif (isset($field['numeric_precision']))
						{
							$field['constraint'] = $field['numeric_precision'].','.$field['numeric_scale'];
						}
					}

					// deal with the different constraint types
					if ($field['data_type'] === 'enum' or $field['data_type'] === 'set' )
					{
						// avoid double quoting
						if (strpos($field['constraint'], '"') !== 0)
						{
							$values = explode(',', $field['constraint']);
							$field['constraint'] = '"'.implode('","', $values).'"';
						}
					}

					// should support field_name:decimal[10,2]
					elseif (in_array($field['data_type'], array('decimal', 'float', 'double')))
					{
						// leave as-is
					}

					// should support any other constraint
					elseif (isset($field['constraint']))
					{
						$field['constraint'] = (int) $field['constraint'];
					}

					// output from list_columns, we're done here!
					$normalized[$field['name']] = $field;
				}
			}
			else
			{
				// we need to split a field string into components
				$field_array = array();

				// Each paramater for a field is seperated by the : character
				$parts = explode(":", $field);

				// We must have the 'name:type' if nothing else!
				if (count($parts) >= 2)
				{
					// make sure we have default values
					$field_array = static::$_field_defaults['_default_'];

					// extract the field name
					$field_array['name'] = array_shift($parts);

					// process the remaining parts
					foreach ($parts as $part_i => $part)
					{
						// split the part
						preg_match('/([a-z0-9_-]+)(?:\[([0-9a-z_\-\,\s]+)\])?/i', $part, $part_matches);
						array_shift($part_matches);

						if ( ! count($part_matches))
						{
							// Move onto the next part, something is wrong here...
							continue;
						}

						// The first option always has to be the field type
						if (empty($field_array['data_type']))
						{
							// determine the field datatype
							$type = $part_matches[0];
							// deal with some generics
							if ($type === 'string')
							{
								$type = 'varchar';
							}
							elseif ($type === 'integer')
							{
								$type = 'int';
							}

							// add the defaults for this datatype
							if (isset(static::$_field_defaults[$type]))
							{
								$field_array = array_merge(static::$_field_defaults[$type], $field_array);
							}

							// deal with any field constraints
							if (isset($part_matches[1]) and $part_matches[1])
							{
								// should support field_name:enum[value1,value2] and field_name:set[value1,value2]
								if ($type === 'enum' or $type === 'set')
								{
									$values = explode(',', $part_matches[1]);
									$part_matches[1] = '"'.implode('","', $values).'"';

									$field_array['constraint'] = $part_matches[1];
								}

								// should support field_name:decimal[10,2]
								elseif (in_array($type, array('decimal', 'float')))
								{
									$field_array['constraint'] = $part_matches[1];
								}

								// should support any other constraint
								else
								{
									$field_array['constraint'] = (int) $part_matches[1];
								}
							}

							// so we can add this next
							$option = 'data_type';
							$part_matches = $type;
						}
						else
						{
							// This allows you to put any number of :option or :option[val] into your field and these will...
							// ... always be passed through to the action making it really easy to add extra options for a field
							$option = array_shift($part_matches);
							if (count($part_matches) > 0)
							{
								$option = $part_matches[0];
							}
							else
							{
								$part_matches = true;
							}
						}

						// deal with some special cases
						switch ($option)
						{
							case 'auto_increment':
							case 'null':
							case 'unsigned':
								$part_matches = (bool) $part_matches;
								break;
						}

						$field_array[$option] = $part_matches;
					}

					$normalized[$field_array['name']] = $field_array;
				}
				else
				{
					// Invalid field passed in
					continue;
				}
			}
		}

		// Check if we have a primary key
		$pk = false;
		foreach ($args as $arg)
		{
			if (isset($arg['indexes']))
			{
				foreach ($arg['indexes'] as $idx)
				{
					if ($idx['primary'])
					{
						$pk = true;
						break;
					}
				}
			}
		}

		// keep track of the primary keys added
		$pk_counter = 0;

		// add a PK if none are present
		if ( ! $pk and ! \Cli::option('no-standardisation'))
		{
			// define the default key column
			$normalized['id'] = array('name' => 'id', 'data_type' => 'int', 'unsigned' => true, 'null' => false, 'auto_increment' => true, 'constraint' => '11');

			// and it's primary index
			$normalized['id']['indexes'] = array('PRIMARY' => array(
				'name' => 'PRIMARY', 'column' => 'id', 'order' => strval(++$pk_counter), 'type' => 'BTREE', 'primary' => true, 'unique' => true, 'null' => false, 'ascending' => true,
			));
		}
		elseif ($normalized['id'] === null)
		{
			// remove the dummy
			unset($normalized['id']);
		}

		// some other optional columns in case of ORM
		if ( ! \Cli::option('crud'))
		{
			$time_type = (\Cli::option('mysql-timestamp')) ? 'timestamp' : 'int';
			$no_timestamp_default = false;

			// closure used to add a new field
			$add_field = function($args, $name, $type, $options = array()) use($pk_counter) {

				// create the field
				$field = static::$_field_defaults['_default_'];

				// add the defaults for this datatype
				if (isset(static::$_field_defaults[$type]))
				{
					$field = array_merge(static::$_field_defaults[$type], $field);
				}

				// add the data
				$field['name'] = $name;
				$field['type'] = $type;
				$field['data_type'] = $type;

				// need to add an index?
				if (isset($options['key']))
				{
					// add a primary key
					if ($options['key'] == 'PRI')
					{
						$field['indexes'] = array('PRIMARY' => array(
							'name' => 'PRIMARY', 'column' => $name, 'order' => strval(++$pk_counter), 'type' => 'BTREE', 'primary' => true, 'unique' => true, 'null' => false, 'ascending' => true,
						));
					}

					unset($options['key']);
				}

				// return the result
				return array_merge($args, array($name => array_merge($field, $options)));
			};


			// additional column for soft-delete models
			if ( \Cli::option('soft-delete'))
			{
				$deleted_at = \Cli::option('deleted-at', 'deleted_at');
				is_string($deleted_at) or $deleted_at = 'deleted_at';
				if ( ! isset($normalized[$deleted_at]))
				{
					$normalized = $add_field($normalized, $deleted_at, $time_type, array('null' => true, 'unsigned' => true));
				}
			}

			// additional column for temporal models
			elseif (\Cli::option('temporal'))
			{
				$temporal_start = \Cli::option('temporal-start', 'temporal_start');
				is_string($temporal_start) or $temporal_start = 'temporal_start';
				if ( ! isset($normalized[$temporal_start]))
				{
					$normalized = $add_field($normalized, $temporal_start, $time_type, array('key' => 'PRI', 'null' => true, 'unsigned' => true));
				}

				$temporal_end = \Cli::option('temporal-end', 'temporal_end');
				is_string($temporal_end) or $temporal_end = 'temporal_end';
				if ( ! isset($normalized[$temporal_end]))
				{
					$normalized = $add_field($normalized, $temporal_end, $time_type, array('key' => 'PRI', 'null' => true, 'unsigned' => true));
				}

				\Cli::set_option('no-timestamp', true);
			}

			// additional columns for nestedset models
			elseif (\Cli::option('nestedset'))
			{
				if ($title = \Cli::option('title', false))
				{
					is_string($title) or $title = 'title';
					if ( ! isset($normalized[$title]))
					{
						$normalized = $add_field($normalized, $title, 'varchar', array('null' => true, 'constraint' => '50'));
					}
				}

				if ($tree_id = \Cli::option('tree-id', false))
				{
					is_string($tree_id) or $tree_id = 'tree_id';
					if ( ! isset($normalized[$tree_id]))
					{
						$normalized = $add_field($normalized, $tree_id, 'int', array('constraint' => '11', 'unsigned' => true));
					}
				}

				$left_id = \Cli::option('left-id', 'left_id');
				is_string($left_id) or $left_id = 'left_id';
				if ( ! isset($normalized[$left_id]))
				{
					$normalized = $add_field($normalized, $left_id, 'int', array('constraint' => '11', 'unsigned' => true));
				}

				$right_id = \Cli::option('right-id', 'right_id');
				is_string($right_id) or $right_id = 'right_id';
				if ( ! isset($normalized[$right_id]))
				{
					$normalized = $add_field($normalized, $right_id, 'int', array('constraint' => '11', 'unsigned' => true));
				}
			}

			if ( ! \Cli::option('no-timestamp') and  ! \Cli::option('no-standardisation'))
			{
				$created_at = \Cli::option('created-at', 'created_at');
				is_string($created_at) or $created_at = 'created_at';
				if ( ! isset($normalized[$created_at]))
				{
					$normalized = $add_field($normalized, $created_at, $time_type, array('null' => true, 'unsigned' => true));
				}

				$updated_at = \Cli::option('updated-at', 'updated_at');
				is_string($updated_at) or $updated_at = 'updated_at';
				if ( ! isset($normalized[$updated_at]))
				{
					$normalized = $add_field($normalized, $updated_at, $time_type, array('null' => true, 'unsigned' => true));
				}
			}
		}

		// return the normalized result
		return $normalized;
	}

	// Helper methods

	/**
	 *
	 */
	private static function _find_migration_number()
	{
		$base_path = APPPATH;

		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module ' . $module . ' was not found within any of the defined module paths');
			}
		}

		foreach(new \GlobIterator($base_path .'migrations/*_*.php') as $file)
		{
			$migrations[] = $file->getPathname();
		}
		if ( ! empty($migrations))
		{
			sort($migrations);
			list($last) = explode('_', basename(end($migrations)));
		}
		else
		{
			$last = 0;
		}

		return str_pad($last + 1, 3, '0', STR_PAD_LEFT);
	}

	/**
	 *
	 */
	private static function _update_current_version($version)
	{
		if (is_file($app_path = APPPATH.'config'.DS.'migrations.php'))
		{
			$contents = file_get_contents($app_path);
		}
		elseif (is_file($core_path = COREPATH.'config'.DS.'migrations.php'))
		{
			$contents = file_get_contents($core_path);
		}
		else
		{
			throw new \Exception('Config file core/config/migrations.php');
			exit;
		}

		$contents = preg_replace("#('version'[ \t]+=>)[ \t]+([0-9]+),#i", "$1 $version,", $contents);

		static::create($app_path, $contents, 'config');
	}

	/**
	 *
	 */
	private static function _create_test($type, $class_name, $base_path, $nav_item = '')
	{
		$filepath = $base_path.strtolower('tests'.DS.$type.DS.ucwords($class_name));
		if ( ! empty($nav_item) and $type === 'View')
		{
			$filepath = $filepath.DS.strtolower($nav_item);
			$class_name = $class_name.'_'.ucwords($nav_item);
		}
		$output = <<<TEST
<?php

class Test_{$type}_{$class_name} extends TestCase
{
}
TEST;

		static::create($filepath.'.php', $output, 'test');
	}
}

/* End of file oil/classes/generate.php */
