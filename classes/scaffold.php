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
 * Oil\Scaffold Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 */
class Scaffold
{
	public static function _init()
	{
		Generate::$scaffolding = true;
	}

	public static function generate($args, $subfolder = 'orm')
	{
		$data = array();

		$subfolder = trim($subfolder, '/');
		if ( ! is_dir(PKGPATH.'oil/views/'.$subfolder))
		{
			throw new Exception('The subfolder for scaffolding templates does not exist or is spelled wrong: '.$subfolder.' ');
		}

		// Go through all arguments after the first and make them into field arrays
		$data['fields'] = array();
		foreach (array_slice($args, 1) as $arg)
		{
			// Parse the argument for each field in a pattern of name:type[constraint]
			preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

			$data['fields'][] = array(
				'name'       => \Str::lower($matches[1]),
				'type'       => isset($matches[2]) ? $matches[2] : 'string',
				'constraint' => isset($matches[4]) ? $matches[4] : null
			);
		}

		$name = array_shift($args);
		$underscored_name = str_replace(DS, '_', $name);

		$singular_class = \Inflector::classify($underscored_name);
		$plural_class = \Inflector::classify(\Inflector::pluralize($underscored_name), false);

		// Either something[s] or folder/something[s]
		$controller_path = str_replace(
			array('_', '-'),
			DS,
			\Inflector::pluralize(\Str::lower($name))
		);

		$model_path = str_replace(
			array('_', '-'),
			DS,
			\Inflector::singularize(\Str::lower($name))
		);

		$data['controller_class'] = $plural_class;
		$data['model_class'] = $singular_class;
		$data['view_path'] = $controller_path;
		$data['uri'] = $controller_path;
		$data['include_timestamps'] = ( ! \Cli::option('no-timestamp', false));

		// If a folder is used, the entity is the last part
		$name_parts = explode(DS, $name);
		$data['singular_name'] = \Inflector::singularize(end($name_parts));
		$data['plural_name'] = \Inflector::pluralize($data['singular_name']);
		$data['table'] = \Inflector::tableize($data['model_class']);

		/** Generate the Migration **/
		$migration_args = $args;
		array_unshift($migration_args, 'create_'.\Inflector::pluralize(\Str::lower($name)));
		Generate::migration($migration_args, false);

		/** Generate the Model **/
		$model = \View::forge($subfolder.'/scaffold/model', $data);
		Generate::create(
			APPPATH.'classes/model/'.$model_path.'.php',
			$model,
			'model'
		);

		/** Generate the Controller **/
		$controller = \View::forge($subfolder.'/scaffold/controller', $data);
		$controller->actions = array(
			array(
				'name'   => 'index',
				'params' => '',
				'code'   => \View::forge($subfolder.'/scaffold/actions/index', $data),
			),
			array(
				'name'   => 'view',
				'params' => '$id = null',
				'code'   => \View::forge($subfolder.'/scaffold/actions/view', $data),
			),
			array(
				'name'   => 'create',
				'params' => '$id = null',
				'code'   => \View::forge($subfolder.'/scaffold/actions/create', $data),
			),
			array(
				'name'   => 'edit',
				'params' => '$id = null',
				'code'   => \View::forge($subfolder.'/scaffold/actions/edit', $data),
			),
			array(
				'name'   => 'delete',
				'params' => '$id = null',
				'code'   => \View::forge($subfolder.'/scaffold/actions/delete', $data),
			),
		);
		Generate::create(
			APPPATH.'classes/controller/'.$controller_path.'.php',
			$controller,
			'controller'
		);

		// Create each of the views
		foreach (array('index', 'view', 'create', 'edit', '_form') as $view)
		{
			Generate::create(
				APPPATH.'views/'.$controller_path.'/'.$view.'.php',
				\View::forge($subfolder.'/scaffold/views/'.$view, $data),
				'view'
			);
		}

		// Add the default template if it doesnt exist
		if ( ! file_exists($app_template = APPPATH.'views/template.php'))
		{
			Generate::create($app_template, file_get_contents(PKGPATH.'oil/views/'.$subfolder.'/template.php'), 'view');
		}

		Generate::build();
	}

}

/* End of file oil/classes/scaffold.php */