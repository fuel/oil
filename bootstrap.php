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

\Autoloader::add_classes(array(
	'Oil\\Command'                    => __DIR__.'/classes/command.php',
	'Oil\\Console'                    => __DIR__.'/classes/console.php',
	'Oil\\Exception'                  => __DIR__.'/classes/exception.php',
	'Oil\\Generate'                   => __DIR__.'/classes/generate.php',
	'Oil\\Generate_Migration_Actions' => __DIR__.'/classes/generate/migration/actions.php',
	'Oil\\Generate_Admin'             => __DIR__.'/classes/generate/admin.php',
	'Oil\\Generate_Scaffold'          => __DIR__.'/classes/generate/scaffold.php',
	'Oil\\Package'                    => __DIR__.'/classes/package.php',
	'Oil\\Refine'                     => __DIR__.'/classes/refine.php',
));
