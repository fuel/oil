<?php
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuraion, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return array(
	'phpunit' => array(

		/**
		 * These phpunit settings allow oil to run your project's phpunit
		 * tests. If you've installed phpunit as a global install via
		 * pear, then the defaults don't need to be changed. But if you've
		 * installed phpunit via some other method such as Composer,
		 * you'll need to update these settings to reflect that.
		 *
		 * autoload_path - the path to PHPUnit's Autoload.php file.
		 * binary_path - the full path you'd type into the command line
		 *  to run phpunit from an arbitrary directory.
		 *
		 * For example, if you've installed phpunit via Composer, your
		 * autoload_path will probably be something like:
		 *     'autoload_path' => VENDORPATH.'phpunit/phpunit/PHPUnit/Autoload.php',
		 * and your binary path will probably be something like:
		 *     'binary_path' => VENDORPATH.'bin/phpunit',
		 *
		 * At present, there is no support for phpunit.phar.
		 */

		'autoload_path' => 'PHPUnit/Autoload.php' ,
		'binary_path'   => 'phpunit' ,

	),
);
