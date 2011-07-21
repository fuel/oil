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
 * Oil\Cli Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Command
{
	public static function init($args)
	{
		// Remove flag options from the main argument list
		for ($i =0; $i < count($args); $i++)
		{
			if (strpos($args[$i], '-') === 0)
			{
				unset($args[$i]);
			}
		}

		try
		{
			if ( ! isset($args[1]))
			{
				if (\Cli::option('v', \Cli::option('version')))
				{
					\Cli::write('Fuel: ' . \Fuel::VERSION);
					return;
				}

				static::help();
				return;
			}

			switch ($args[1])
			{
				case 'g':
				case 'generate':

					$action = isset($args[2]) ? $args[2]: 'help';

					$subfolder = 'default';
					if (is_int(strpos($action, 'scaffold/')))
					{
						$subfolder = str_replace('scaffold/', '', $action);
						$action = 'scaffold';
					}

					switch ($action)
					{
						case 'controller':
						case 'model':
						case 'views':
						case 'migration':
							call_user_func('Oil\Generate::'.$action, array_slice($args, 3));
						break;

						case 'scaffold':
							call_user_func('Oil\Scaffold::generate', array_slice($args, 3), $subfolder);
						break;

						default:
							Generate::help();
					}

				break;

				case 'c':
				case 'console':
					new Console;

				case 'r':
				case 'refine':

					// Developers of third-party tasks may not be displaying PHP errors. Report any error and quit
					set_error_handler(function($errno, $errstr, $errfile, $errline){
						\Cli::error("Error: {$errstr} in $errfile on $errline");
						\Cli::beep();
						exit;
					});

					$task = isset($args[2]) ? $args[2] : null;

					call_user_func('Oil\Refine::run', $task, array_slice($args, 3));
				break;

				case 'p':
				case 'package':

					$action = isset($args[2]) ? $args[2]: 'help';

					switch ($action)
					{
						case 'install':
						case 'uninstall':
							call_user_func_array('Oil\Package::'.$action, array_slice($args, 3));
						break;

						default:
							Package::help();
					}

				break;

				case 't':
				case 'test':

					// Suppressing this because if the file does not exist... well thats a bad thing and we can't really check
					// I know that supressing errors is bad, but if you're going to complain: shut up. - Phil
					@include_once('PHPUnit/Autoload.php');

					// Attempt to load PHUnit.  If it fails, we are done.
					if ( ! class_exists('PHPUnit_Framework_TestCase'))
					{
						throw new Exception('PHPUnit does not appear to be installed.'.PHP_EOL.PHP_EOL."\tPlease visit http://phpunit.de and install.");
					}

					// Override PHPUnit's help command with our own
					if ( \Cli::option('help'))
					{
						static::help_test();
						break;
					}

					// CD to the root of Fuel and call up phpunit with a path to our config
					$command = 'cd '.DOCROOT.'; phpunit -c "'.COREPATH.'phpunit.xml"';

					// Respect the code coverage options
					\Cli::option('coverage-html') and $command .= ' --coverage-html '.\Cli::option('coverage-html');
					\Cli::option('coverage-clover') and $command .= ' --coverage-clover '.\Cli::option('coverage-clover');

					// Respect the agile documentation options
					\Cli::option('testdox-html') and $command .= ' --testdox-html '.\Cli::option('testdox-html');
					\Cli::option('testdox-text') and $command .= ' --testdox-text '.\Cli::option('testdox-text');

					// Respect the group options
					\Cli::option('filter') and $command .= ' --filter '.\Cli::option('filter');
					\Cli::option('group') and $command .= ' --group '.\Cli::option('group');
					\Cli::option('exclude-group') and $command .= ' --exclude-group '.\Cli::option('exclude-group');
					\Cli::option('list-groups') and $command .= ' --list-groups';

					// Respect the repeat option
					\Cli::option('repeat') and $command .= ' --repeat '.\Cli::option('repeat');

					// Respect the report options
					\Cli::option('tap') and $command .= ' --tap';
					\Cli::option('testdox') and $command .= ' --testdox';

					// Respect the test options
					\Cli::option('stderr') and $command .= ' --stderr';
					\Cli::option('stop-on-error') and $command .= ' --stop-on-error';
					\Cli::option('stop-on-failure') and $command .= ' --stop-on-failure';
					\Cli::option('stop-on-skipped') and $command .= ' --stop-on-skipped';
					\Cli::option('stop-on-incomplete') and $command .= ' --stop-on-incomplete';
					\Cli::option('strict') and $command .= ' --strict';

					// Respect assorted remaining options
					\Cli::option('syntax-check') and $command .= ' --syntax-check';
					\Cli::option('version') and $command .= ' --version';
					\Cli::option('debug') and $command .= ' --debug';

					passthru($command);

				break;

				default:

					static::help();
			}
		}

		catch (Exception $e)
		{
			\Cli::error('Error: '.$e->getMessage());
			\Cli::beep();
		}
	}

	public static function help()
	{
		echo <<<HELP

Usage:
  php oil [console|generate|help|test|package]

Runtime options:
  -f, [--force]    # Overwrite files that already exist
  -s, [--skip]     # Skip files that already exist
  -q, [--quiet]    # Supress status output

Description:
  The 'oil' command can be used in several ways to facilitate quick development, help with
  testing your application and for running Tasks.

Documentation:
  http://fuelphp.com/docs/packages/oil/intro.html

HELP;

	}

	public static function help_test()
	{
		passthru('phpunit --version');

		echo <<<HELP
Usage: php oil test [switches]

  --coverage-html=<dir>     Generate code coverage report in HTML format.
  --coverage-clover=<dir>   Write code coverage data in Clover XML format.

  --testdox-html=<file>	    Write agile documentation in HTML format to file.
  --testdox-text=<file>     Write agile documentation in Text format to file.

  --filter="<pattern>"      Filter which tests to run.
  --group=...               Only runs tests from the specified group(s).
  --exclude-group=...       Exclude tests from the specified group(s).
  --list-groups             List available test groups.

  --repeat=<times>          Runs the test(s) repeatedly.

  --tap                     Report test execution progress in TAP format.
  --testdox                 Report test execution progress in TestDox format.

  --stderr                  Write to STDERR instead of STDOUT.
  --stop-on-error           Stop execution upon first error.
  --stop-on-failure         Stop execution upon first error or failure.
  --stop-on-skipped         Stop execution upon first skipped test.
  --stop-on-incomplete      Stop execution upon first incomplete test.
  --strict                  Mark a test as incomplete if no assertions are made.

  --syntax-check            Try to check source files for syntax errors.

  --help                    Prints this usage information.
  --version                 Prints the version and exits.

  --debug                   Output debugging information.
HELP;

	}
}

/* End of file oil/classes/cli.php */
