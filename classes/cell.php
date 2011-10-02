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
 * Oil\Package Class
 *
 * @package		Fuel
 * @subpackage	Oil
 * @category	Core
 * @author		Phil Sturgeon
 */
class Cell
{
	protected static $_protected = array('auth', 'oil', 'orm');
	protected static $_api_url = 'local.station.fuelphp.com/api/';
	
	protected static $_git_binary = 'git';
	protected static $_hg_binary = 'hg';

	public static function install($package = null)
	{
		// Make sure something is set
		if ($package === null)
		{
			static::help();
			return;
		}

		$version = \Cli::option('version', 'master');

		// Check to see if this package is already installed
		if (is_dir(PKGPATH . $package))
		{
			throw new Exception('Package "' . $package . '" is already installed.');
			return;
		}
		
		$request_url = 'http://'.static::$_api_url.'cells/show.json?name='.urlencode($package);
		$response = json_decode(@file_get_contents($request_url), true);
		
		if ( ! $response)
		{
			throw new Exception('No response from the API. Perhaps check your internet connection?');
		}
		
		if (empty($response['cell']))
		{
			throw new Exception('Could not find the cell "' . $package . '".');
		}
		
		// Now, lets get this package

		// If a direct download is requested, or git is unavailable, download it!
		if (\Cli::option('git-submodule'))
		{
			static::_download_package_zip($zip_url, $package, $version);
		}

		// Otherwise, get your clone on baby!
		else
		{
			static::_clone_package_repo($source, $package, $version);
		}

	}

	public static function all()
	{
		$request_url = 'http://'.static::$_api_url.'cells/list.json';
		$response = json_decode(@file_get_contents($request_url), true);

		if (empty($response['cells']))
		{
			throw new Exception('No cells were found with this name.');
		}

		foreach ($response['cells'] as $cell)
		{
			\Cli::write(\Cli::color($cell['name'], 'yellow').' '.\Cli::color($cell['current_version'], 'white').' - '.$cell['summary']);
		}
	}

	public static function search($name)
	{
		$request_url = 'http://'.static::$_api_url.'cells/search.json?name='.urlencode($name);
		$response = json_decode(file_get_contents($request_url), true);

		if (empty($response['cells']))
		{
			throw new Exception('No cells were found with this name.');
		}

		foreach ($response['cells'] as $cell)
		{
			\Cli::write(\Cli::color($cell['name'], 'yellow').' '.\Cli::color($cell['current_version'], 'white').' - '.$cell['summary']);
		}
	}


	public static function uninstall($package)
	{
		$package_folder = PKGPATH . $package;

		// Check to see if this package is already installed
		if (in_array($package, static::$_protected))
		{
			throw new Exception('Package "' . $package . '" cannot be uninstalled.');
			return false;
		}

		// Check to see if this package is already installed
		if ( ! is_dir($package_folder))
		{
			throw new Exception('Package "' . $package . '" is not installed.');
			return false;
		}

		\Cli::write('Package "' . $package . '" was uninstalled.', 'yellow');

		\File::delete_dir($package_folder);
	}

	public static function help()
	{
		$output = <<<HELP

Usage:
  oil cell install <cell-name>

Description:
  Packages containing extra functionality can be downloaded (or git cloned) simply with
  the following commands.

Runtime options:
  --git-submodule       # Install as a git submodule

Examples:
  oil cell install <cell-name>
  oil cell uninstall <cell-name>

Documentation:
  http://fuelphp.com/docs/packages/oil/cell.html
HELP;
		\Cli::write($output);

	}


	private static function _use_git()
	{
		exec('which git', $output);

		// If this is a valid path to git, use it instead of just "git"
		if (file_exists($line = trim(current($output))))
		{
			static::$git = $line;
		}

		unset($output);

		// Double check git is installed (windows will fail step 1)
		exec(static::$git . ' --version', $output);

		preg_match('#^(git version)#', current($output), $matches);

		// If we have a match, use Git!
		return ! empty($matches[0]);
	}

	private static function _download_package_zip($zip_url, $package, $version)
	{
		\Cli::write('Downloading package: ' . $zip_url);

		// Make the folder so we can extract the ZIP to it
		mkdir($tmp_folder = APPPATH . 'tmp/' . $package . '-' . time());

		$zip_file = $tmp_folder . '.zip';
		@copy($zip_url, $zip_file);

		$unzip = new \Unzip;
		$files = $unzip->extract($zip_file, $tmp_folder);

		// Grab the first folder out of it (we dont know what it's called)
		list($tmp_package_folder) = glob($tmp_folder.'/*', GLOB_ONLYDIR);

		$package_folder = PKGPATH . $package;

		// Move that folder into the packages folder
		rename($tmp_package_folder, $package_folder);

		unlink($zip_file);
		rmdir($tmp_folder);

		foreach ($files as $file)
		{
			$path = str_replace($tmp_package_folder, $package_folder, $file);
			chmod($path, octdec(755));
			\Cli::write("\t" . $path);
		}
	}

	public static function _clone_package_repo($source, $package, $version)
	{
		$repo_url = 'git://' . rtrim($source, '/').'/fuel-'.$package . '.git';

		\Cli::write('Downloading package: ' . $repo_url);

		$package_folder = PKGPATH . $package;

		// Clone to the package path
		passthru(static::$_git_binary . ' clone ' . $repo_url . ' ' . $package_folder);

		passthru(static::$_git_binary .' add ' . $package_folder . '/');

		\Cli::write('');
	}
}

/* End of file package.php */