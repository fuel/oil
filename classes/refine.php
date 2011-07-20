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
 * Oil\Refine Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Refine
{
	public static function run($task, $args)
	{
		// Make sure something is set
		if ($task === null OR $task === 'help')
		{
			static::help();
			return;
		}

		// Just call and run() or did they have a specific method in mind?
		list($task, $method)=array_pad(explode(':', $task), 2, 'run');

		$task = ucfirst(strtolower($task));

		// Find the task
		if ( ! $file = \Fuel::find_file('tasks', $task))
		{
			$files = \Fuel::list_files('tasks');
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
				throw new Exception(sprintf('Task "%s" does not exist. Did you mean "%s"?', strtolower($task), current($possibilities)));
			}
			else
			{
				throw new Exception(sprintf('Task "%s" does not exist.', strtolower($task)));
			}
			
			return;
		}

		require_once $file;

		$task = '\\Fuel\\Tasks\\'.$task;

		$new_task = new $task;

		// The help option hs been called, so call help instead
		if (\Cli::option('help') && is_callable(array($new_task, 'help')))
		{
			$method = 'help';
		}

		if ($return = call_user_func_array(array($new_task, $method), $args))
		{
			\Cli::write($return);
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
		} else {
			$output_available_tasks = "    (none found)";
		}
		
		$output = <<<HELP

Usage:
    php oil [r|refine] <taskname>

Description:
    Tasks are classes that can be run through the the command line or set up as a cron job.

Available tasks:
$output_available_tasks
Documentation:
    http://fuelphp.com/docs/packages/oil/refine.html
HELP;
		\Cli::write($output);

	}
	
	/**
	 * Find all of the task classes in the system and use reflection to discover the
	 * commands we can call.
	 *
	 * @return array $taskname => array($taskmethods)
	 **/
	protected static function _discover_tasks() {
		$result = array();
		$files = \Fuel::list_files('tasks');
		
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
						$result[$task_name][] = $method->name;
					}
				}
			}
		}
		
		return $result;
	}
}

/* End of file oil/classes/refine.php */
