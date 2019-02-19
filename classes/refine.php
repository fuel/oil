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
 * Oil\Refine Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 */
class Refine
{
	public static function run($task, $args = array())
	{
		$task = strtolower($task);

		// Make sure something is set
		if (empty($task) or $task === 'help')
		{
			static::help();
			return;
		}

		$module = false;
		list($module, $task) = array_pad(explode('::', $task), 2, null);

		if ($task === null)
		{
			$task = $module;
			$module = false;
		}

		if ($module)
		{
			try
			{
				\Module::load($module);
				$path = \Module::exists($module);
				\Finder::instance()->add_path($path, -1);
			}
			catch (\FuelException $e)
			{
				throw new Exception(sprintf('Module "%s" does not exist.', $module));
			}
		}

		// Just call and run() or did they have a specific method in mind?
		list($task, $method) = array_pad(explode(':', $task), 2, 'run');

		// Find the task
		if ( ! $file = \Finder::search('tasks', $task))
		{
			$files = \Finder::instance()->list_files('tasks');
			$possibilities = array();
			foreach($files as $file)
			{
				$possible_task = pathinfo($file, \PATHINFO_FILENAME);
				$difference = levenshtein($possible_task, $task);
				$possibilities[$difference] = $possible_task;
			}

			ksort($possibilities);

			if ($possibilities and current($possibilities) <= 5)
			{
				throw new Exception(sprintf('Task "%s" does not exist. Did you mean "%s"?', $task, current($possibilities)));
			}
			else
			{
				throw new Exception(sprintf('Task "%s" does not exist.', $task));
			}

			return;
		}

		require_once $file;

		$task = '\\Fuel\\Tasks\\'.ucfirst($task);

		$new_task = new $task;

		// The help option has been called, so call help instead
		if ((\Cli::option('help') or $method == 'help') and is_callable(array($new_task, 'help')))
		{
			$method = 'help';
		}
		else
		{
			// if the task has an init method, call it now
			is_callable($task.'::_init') and $task::_init();
		}

		if (is_callable(array($new_task, $method)))
		{
			if ($return = call_fuel_func_array(array($new_task, $method), $args))
			{
				\Cli::write($return);
			}
		}
		else
		{
			\Cli::write(sprintf('Task "%s" does not have a command called "%s".', $task, $method));

			\Cli::write("\nDid you mean:\n");
			$reflect = new \ReflectionClass($new_task);

			// Ensure we only pull out the public methods
			$methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);
			if (count($methods) > 0)
			{
				foreach ($methods as $method)
				{
					if (strpos($method->name, '_') !== 0)
					{
						\Cli::write(sprintf("php oil [r|refine] %s:%s", $reflect->getShortName(), $method->name));

					}
				}
			}
		}
	}

	public static function help()
	{
	    // Build a list of possible tasks for the help output
		$tasks = self::_discover_tasks();
		if (count($tasks) > 0)
		{
			$output_available_tasks = "";

			foreach ($tasks as $task => $options)
			{
				foreach ($options as $option)
				{
				    $option = ($option == "run") ? "" : ":$option";
					$output_available_tasks .= "    php oil refine $task$option\n";
				}
			}
		}

		else
		{
			$output_available_tasks = "    (none found)";
		}

		$output = <<<HELP

Usage:
    php oil [r|refine] <taskname>

Description:
    Tasks are classes that can be run through the command line or set up as a cron job.

Available tasks:
$output_available_tasks
Documentation:
    https://docs.fuelphp.com/packages/oil/refine.html
HELP;
		\Cli::write($output);

	}

	/**
	 * Find all of the task classes in the system and use reflection to discover the
	 * commands we can call.
	 *
	 * @return array $taskname => array($taskmethods)
	 **/
	protected static function _discover_tasks()
	{
		$result = array();
		$files = \Finder::instance()->list_files('tasks');

		if (count($files) > 0)
		{
			foreach ($files as $file)
			{
				$task_name = str_replace('.php', '', basename($file));
				$class_name = '\\Fuel\\Tasks\\'.$task_name;

				require $file;

				$reflect = new \ReflectionClass($class_name);

				// Ensure we only pull out the public methods
				$methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);

				$result[$task_name] = array();

				if (count($methods) > 0)
				{
					foreach ($methods as $method)
					{
						strpos($method->name, '_') !== 0 and $result[$task_name][] = $method->name;
					}
				}
			}
		}

		return $result;
	}
}
