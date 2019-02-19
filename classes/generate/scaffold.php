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
 * Oil\Scaffold Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 */
class Generate_Scaffold
{
	public static $fields_regex = '/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i';

	public static $view_subdir = 'scaffolding/';

	public static $controller_prefix = '';
	public static $model_prefix = '';

	public static $controller_parent = 'Controller_Template';

	public static function _init()
	{
		Generate::$scaffolding = true;
	}

	/**
	 * Forge
	 *
	 * @param   array   Fields mainly
	 * @param   string  Subfolder (or admin "theme") where views are held
	 * @return	mixed
	 */
	public static function forge($args, $subfolder)
	{
		$data = array();

		$subfolder = trim($subfolder, '/');

		if ( ! is_dir(\Package::exists('oil').'views/'.static::$view_subdir.$subfolder))
		{
			throw new Exception('The subfolder for admin templates does not exist or is spelled wrong: '.$subfolder.' ');
		}

		// Go through all arguments after the first and make them into field arrays
		$data['fields'] = array();
		foreach (array_slice($args, 1) as $arg)
		{
			// parse the argument for each field in a pattern of name:type[constraint]
			if (is_string($arg))
			{
				preg_match(static::$fields_regex, $arg, $matches);

				if ( ! isset($matches[1]))
				{
					throw new Exception('Unable to determine the field definition for "'.$arg.'". Ensure they are name:type');
				}

				$data['fields'][] = array(
					'name'       => \Str::lower($matches[1]),
					'type'       => isset($matches[2]) ? $matches[2] : 'string',
					'constraint' => isset($matches[4]) ? $matches[4] : null,
				);
			}

			// argument is an array with a column definition
			elseif (is_array($arg))
			{
				$data['fields'][] = array(
					'name'       => $arg['name'],
					'type'       => $arg['type'],
					'constraint' => $arg['constraint'],
				);
			}

			// huh?
			else
			{
				// skip it
				logger(\Fuel::L_DEBUG, 'Generate_Scaffold::forge(): incorrect argument type passed');
			}
		}

		$name = array_shift($args);

		// Replace / with _ and classify the rest. DO NOT singularize
		$controller_name = \Inflector::classify(static::$controller_prefix.str_replace(DS, '_', $name), false);

		// Replace / with _ and classify the rest. Singularize
		$model_name = \Inflector::classify(static::$model_prefix.str_replace(DS, '_', $name), ! \Cli::option('singular'));

		// Either foo or folder/foo
		$controller_path = str_replace(
			array('_', '-'),
			DS,
			\Str::lower($controller_name)
		);

		// uri's and view paths have forward slashes, DS is a backslash on Windows
		$uri = $view_path = str_replace(DS, '/', $controller_path);

		// Models are always singular, tough!
		$model_path = str_replace(
			array('_', '-'),
			DS,
			\Str::lower($model_name)
		);

		$data['include_timestamps'] = ( ! \Cli::option('no-timestamp', false));

		// If a folder is used, the entity is the last part
		$name_parts = explode(DS, $name);
		$data['singular_name'] = \Cli::option('singular') ? end($name_parts) : \Inflector::singularize(end($name_parts));
		$data['plural_name'] = \Cli::option('singular') ? $data['singular_name'] : \Inflector::pluralize($data['singular_name']);

		$data['table'] = \Inflector::tableize($model_name);
		$data['controller_parent'] = static::$controller_parent;

		/** Generate the Migration **/
		$migration_args = $args;

		// add timestamps to the table if needded
		if ($data['include_timestamps'])
		{
			if (\Cli::option('mysql-timestamp', false))
			{
				$migration_args[] = 'created_at:date:null[1]';
				$migration_args[] = 'updated_at:date:null[1]';
			}
			else
			{
				$migration_args[] = 'created_at:int:null[1]';
				$migration_args[] = 'updated_at:int:null[1]';
			}
		}
		$migration_name = \Cli::option('singular') ? \Str::lower($name) : \Inflector::pluralize(\Str::lower($name));
		array_unshift($migration_args, 'create_'.$migration_name);
		Generate::migration($migration_args, false);

		// Merge some other data in
		$data = array_merge(compact(array('controller_name', 'model_name', 'model_path', 'view_path', 'uri')), $data);

		/** Generate the Model **/
		$model = \View::forge(static::$view_subdir.$subfolder.'/model', $data);

		Generate::create(
			APPPATH.'classes/model/'.$model_path.'.php',
			$model,
			'model'
		);

		/** Generate the Controller **/
		$controller = \View::forge(static::$view_subdir.$subfolder.'/controller', $data);

		$controller->actions = array(
			array(
				'name'   => 'index',
				'params' => '',
				'code'   => \View::forge(static::$view_subdir.$subfolder.'/actions/index', $data),
			),
			array(
				'name'   => 'view',
				'params' => '$id = null',
				'code'   => \View::forge(static::$view_subdir.$subfolder.'/actions/view', $data),
			),
			array(
				'name'   => 'create',
				'params' => '',
				'code'   => \View::forge(static::$view_subdir.$subfolder.'/actions/create', $data),
			),
			array(
				'name'   => 'edit',
				'params' => '$id = null',
				'code'   => \View::forge(static::$view_subdir.$subfolder.'/actions/edit', $data),
			),
			array(
				'name'   => 'delete',
				'params' => '$id = null',
				'code'   => \View::forge(static::$view_subdir.$subfolder.'/actions/delete', $data),
			),
		);

		Generate::create(
			APPPATH.'classes'.DS.'controller'.DS.$controller_path.'.php',
			$controller,
			'controller'
		);

		// do we want csrf protection in our forms?
		$data['csrf'] = \Cli::option('csrf') ? true : false;

		// Create each of the views
		foreach (array('index', 'view', 'create', 'edit', '_form') as $view)
		{
			Generate::create(
				APPPATH.'views'.DS.$controller_path.DS.$view.'.php',
				\View::forge(static::$view_subdir.$subfolder.'/views/actions/'.$view, $data),
				'view'
			);
		}

		// If not generating admin files, add the default template if it doesnt exist
		if (static::$view_subdir != 'admin/' and  ! is_file($app_template = APPPATH.'views/template.php'))
		{
			// check if there's a template in app, and if so, use that
			if (is_file(APPPATH.'views/'.static::$view_subdir.$subfolder.'/views/template.php'))
			{
				Generate::create($app_template, file_get_contents(APPPATH.'views/'.static::$view_subdir.$subfolder.'/views/template.php'), 'view');
			}
			else
			{
				Generate::create($app_template, file_get_contents(\Package::exists('oil').'views/'.static::$view_subdir.'template.php'), 'view');
			}
		}

		Generate::build();
	}

}

/* End of file oil/classes/scaffold.php */
