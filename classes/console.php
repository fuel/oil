<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.9-dev
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

namespace Oil;

/**
 * Oil\Console Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Console
{

	protected const MAX_HISTORY = 99;

	protected $history = array();

	public function __construct()
	{
		error_reporting(E_ALL | E_STRICT);

		ini_set("error_log", NULL);
		ini_set("log_errors", 1);
		ini_set("html_errors", 0);
		ini_set("display_errors", 0);

		while (ob_get_level())
		{
			ob_end_clean();
		}

		ob_implicit_flush(true);

		// And, go!
		self::main();
	}

	public static function help()
	{
		$output = <<<HELP

Usage:
  php oil [c|console]

Description:
  Opens a commandline console to your FuelPHP installation. This allows
  you to run any FuelPHP command interactively.

Examples:
  php oil console

Documentation:
  https://fuelphp.com/docs/packages/oil/console.html
HELP;
		\Cli::write($output);

	}

	protected function push_history($line)
	{
		// delimit each line, allowing for copy & paste of history back into Console
		$line .= ';';

		array_push($this->history, $line);
		$this->history = array_slice($this->history, -self::MAX_HISTORY);
	}

	protected function pop_history()
	{
		array_pop($this->history);
	}

	protected function show_history()
	{
		\Cli::write($this->history);
		\Cli::write('');
	}

	protected function main()
	{
		\Cli::write(sprintf(
			'Fuel %s - PHP %s (%s) (%s) [%s]',
			\Fuel::VERSION,
			phpversion(),
			php_sapi_name(),
			self::build_date(),
			PHP_OS
		));

		\Cli::write(array(
			'', 
			'Commands', 
			':q | quit - exit the console', 
			':h | history - show transcript',
			''
		));

		// Loop until they break it
		while (TRUE)
		{
			if (\Cli::$readline_support)
			{
				readline_completion_function(array(__CLASS__, 'tab_complete'));
			}

			if ( ! $__line = rtrim(trim(trim(\Cli::input('>>> ')), PHP_EOL), ';'))
			{
				continue;
			}

			if ($__line == ':q' or $__line == 'quit')
			{
				break;
			}
			elseif ($__line == ':h' or $__line == 'history')
			{
				$this->show_history();
				continue;
			}

			// Add this line to history
			$this->push_history($__line);

			if (\Cli::$readline_support)
			{
				readline_add_history($__line);
			}

			if (self::is_immediate($__line))
			{
				$__line = "return ($__line)";
			}

			ob_start();

			// Unset the previous line and execute the new one
			$random_ret = \Str::random();
			try
			{
				$ret = eval("unset(\$__line); $__line;");
			}
			catch(\Exception $e)
			{
				// Remove last (bad) line from history
				$this->pop_history();

				$ret = $random_ret;
				$__line = $e->getMessage();
			}
			catch(\Error $e)
			{
				// Remove last (bad) line from history
				$this->pop_history();

				$ret = $random_ret;
				$__line = $e->getMessage();
			}
            
			// Error was returned
			if ($ret === $random_ret)
			{
				\Cli::error('Parse Error - ' . $__line);
				\Cli::beep();
			}

			if (ob_get_length() == 0)
			{
				if (is_bool($ret))
				{
					echo $ret ? 'true' : 'false';
				}
				elseif (is_string($ret))
				{
					echo addcslashes($ret, "\0..\11\13\14\16..\37\177..\377");
				}
				elseif ( ! is_null($ret))
				{
					print_r($ret);
				}
			}

			unset($ret);
			$out = ob_get_contents();
			ob_end_clean();

			if ((strlen($out) > 0) && (substr($out, -1) != PHP_EOL))
			{
				$out .= PHP_EOL;
			}

			echo $out;
			unset($out);
		}
	}

	private static function is_immediate($line)
	{
		$skip = array(
			'class', 'declare', 'die', 'echo', 'exit', 'for',
			'foreach', 'function', 'global', 'if', 'include',
			'include_once', 'print', 'require', 'require_once',
			'return', 'static', 'switch', 'unset', 'while',
		);

		$okeq = array('===', '!==', '==', '!=', '<=', '>=');

		$code = '';
		$sq = false;
		$dq = false;

		for ($i = 0; $i < strlen($line); $i++)
		{
			$c = $line[$i];
			if ($c == "'")
			{
				$sq = !$sq;
			}
			elseif ($c == '"')
			{
				$dq = !$dq;
			}

			elseif ( ($sq) || ($dq) && $c == "\\")
			{
				++$i;
			}
			else
			{
				$code .= $c;
			}
		}

		$code = str_replace($okeq, '', $code);
		if (strcspn($code, ';{=') != strlen($code))
		{
			return false;
		}

		$kw = preg_split("/[^a-z0-9_]/i", $code);
		foreach ($kw as $i)
		{
			if (in_array($i, $skip))
			{
				return false;
			}
		}

		return true;
	}

	public static function tab_complete($line, $pos, $cursor)
	{
		$const = array_keys(get_defined_constants());
		$var = array_keys($GLOBALS);
		$func = get_defined_functions();

		foreach ($func["user"] as $i)
		{
			$func["internal"][] = $i;
		}
		$func = $func["internal"];

		return array_merge($const, $var, $func);
	}

	private static function build_date()
	{
		ob_start();
		phpinfo(INFO_GENERAL);

		$x = ob_get_contents();
		ob_end_clean();

		$x = strip_tags($x);
		$x = explode("\n", $x); // PHP_EOL doesn't work on Windows
		$s = array('Build Date => ', 'Build Date ');

		foreach ($x as $i)
		{
			foreach ($s as $j)
			{
				if (substr($i, 0, strlen($j)) == $j)
				{
					return trim(substr($i, strlen($j)));
				}
			}
		}

		return '???';
	}

}

/* End of file oil/classes/console.php */
