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
 * @author		Dan Horrigan
 */
class Scaffold
{
	public static function _init()
	{
		Generate::$scaffolding = true;
	}

	public static function generate($args, $subfolder = 'default')
	{
		$subfolder = trim($subfolder, '/');
		if ( ! is_dir( PKGPATH.'oil/views/'.$subfolder))
		{
			throw new Exception('The subfolder for scaffolding templates doesn\'t exist or is spelled wrong: '.$subfolder.' ');
		}

		// Do this first as there is the largest chance of error here
		Generate::model($args, false);

		// Go through all arguments after the first and make them into field arrays
		$fields = array();
		foreach (array_slice($args, 1) as $arg)
		{
			// Parse the argument for each field in a pattern of name:type[constraint]
			preg_match('/([a-z0-9_]+):([a-z0-9_]+)(\[([0-9]+)\])?/i', $arg, $matches);

			$fields[] = array(
				'name' => \Str::lower($matches[1]),
				'type' => isset($matches[2]) ? $matches[2] : 'string',
				'constraint' => isset($matches[4]) ? $matches[4] : null
			);
		}

		$full_thing = array_shift($args);
		$full_underscores = str_replace(DS, '_', $full_thing);

		// Either something[s] or folder/something[s]
		$data['controller_uri'] = $controller_uri = \Inflector::pluralize(\Str::lower($full_thing));
		$data['controller'] = 'Controller_'.\Inflector::classify(\Inflector::pluralize($full_underscores), false);

		// If a folder is used, the entity is the last part
		$parts = explode(DS, $full_thing);
		$data['singular'] = $singular = \Inflector::singularize(end($parts));
		$data['model'] = $model_name = \Inflector::classify($full_underscores);
		$data['plural'] = $plural = \Inflector::pluralize($singular);
		$data['fields'] = $fields;
		
		$filepath = APPPATH.'classes/controller/'.trim(str_replace(array('_', '-'), DS, $controller_uri), DS).'.php';
		$controller = \View::forge($subfolder.'/scaffold/controller', $data);

		$controller->actions = array(
			array(
				'name'		=> 'index',
				'params'	=> '',
				'code'		=> \View::forge($subfolder.'/scaffold/actions/index', $data),
			),
			array(
				'name'		=> 'view',
				'params'	=> '$id = null',
				'code'		=> \View::forge($subfolder.'/scaffold/actions/view', $data),
			),
			array(
				'name'		=> 'create',
				'params'	=> '$id = null',
				'code'		=> \View::forge($subfolder.'/scaffold/actions/create', $data),
			),
			array(
				'name'		=> 'edit',
				'params'	=> '$id = null',
				'code'		=> \View::forge($subfolder.'/scaffold/actions/edit', $data),
			),
			array(
				'name'		=> 'delete',
				'params'	=> '$id = null',
				'code'		=> \View::forge($subfolder.'/scaffold/actions/delete', $data),
			),
		);

		// Write controller
		Generate::create($filepath, $controller, 'controller');

		// Create each of the views
		foreach (array('index', 'view', 'create', 'edit', '_form') as $view)
		{
			Generate::create(APPPATH.'views/'.$controller_uri.'/'.$view.'.php', \View::forge($subfolder.'/scaffold/views/'.$view, $data), 'view');
		}

		// Add the default template if it doesnt exist
		if ( ! file_exists($app_template = APPPATH . 'views/template.php'))
		{
			Generate::create($app_template, file_get_contents(PKGPATH . 'oil/views/default/template.php'), 'view');
		}

		Generate::build();
	}

}

/* End of file oil/classes/scaffold.php */
