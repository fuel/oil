<?php
/**
 * Fuel
 *
 * Fuel is a fast, lightweight, community driven PHP5 framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2014 Fuel Development Team
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
class Generate_Admin extends Generate_Scaffold
{
	public static $view_subdir = 'admin/';

	public static $controller_prefix = 'Admin_';
	public static $model_prefix = '';

	public static $controller_parent = 'Admin';

	public static function _init()
	{
		static::$controller_parent = \Config::get('controller_prefix', 'Controller_').static::$controller_parent;

		parent::_init();
	}

	public static function forge($args, $subfolder)
	{
		$default_files = array(
			array(
				'source' => $subfolder.'/controllers/base.php',
				'location' => 'classes/controller/base.php',
				'type' => 'controller',
			),
			array(
				'source' => $subfolder.'/controllers/admin.php',
				'location' => 'classes/controller/admin.php',
				'type' => 'controller',
			),
			array(
				'source' => 'template.php',
				'location' => 'views/admin/template.php',
				'type' => 'views',
			),
			array(
				'source' => 'dashboard.php',
				'location' => 'views/admin/dashboard.php',
				'type' => 'views',
			),
			array(
				'source' => 'login.php',
				'location' => 'views/admin/login.php',
				'type' => 'views',
			),
		);

		$base_path = APPPATH;
		$data = array(
			'Module'    => '',
			'namespace' => '',
			'module_ds' => '',
			'module_bs' => '',
			'action_url_segment' => 2,
			'base_path' => 'APPPATH',
		);

		// Check if a migration with this name already exists
		if ($module = \Cli::option('module'))
		{
			if ( ! ($base_path = \Module::exists($module)) )
			{
				throw new Exception('Module '.$module.' was not found within any of the defined module paths');
			}
			
			$data['Module'] = ucfirst($module);
			$data['namespace'] = 'namespace '.$data['Module'].';';
			$data['module_ds'] = $module.'/';
			$data['module_bs'] = $data['Module'].'\\';
			$data['action_url_segment'] = 3;
			$data['base_path'] = "\Module::exists('$module')";
		}

		foreach ($default_files as $file)
		{
			// check if there's a template in app, and if so, use that
			if (is_file($base_path.'views/'.static::$view_subdir.$file['source']))
			{
				Generate::create($base_path.$file['location'], file_get_contents($base_path.'views/'.static::$view_subdir.$file['source']), $file['type']);
			}
			else
			{
				$file_contents = \View::forge(\Package::exists('oil').'views/'.static::$view_subdir.$file['source'], $data, false);
				Generate::create($base_path.$file['location'], $file_contents, $file['type']);
			}
		}

		Generate::build();
		parent::forge($args, $subfolder);
	}
}

/* End of file oil/classes/generate/admin.php */
