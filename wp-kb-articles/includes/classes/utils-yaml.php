<?php
/**
 * YAML Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly.');

	use Symfony\Component\Yaml\Yaml;

	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Yaml.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Parser.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Inline.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Dumper.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Escaper.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Unescaper.php';

	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Exception/ExceptionInterface.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Exception/RuntimeException.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Exception/DumpException.php';
	require dirname(dirname(dirname(__FILE__))).'/submodules/yaml/Exception/ParseException.php';

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_yaml'))
	{
		/**
		 * YAML Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_yaml extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150201 Adding full YAML parser.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * YAML parser.
			 *
			 * @since 150201 Adding full YAML parser.
			 *
			 * @param string $yaml Input YAML to parse.
			 *
			 * @return array YAML config file to array.
			 */
			public function parse($yaml)
			{
				try // Catch exceptions.
				{
					$array = Yaml::parse($yaml);
				}
				catch(\exception $exception)
				{
					$array = array();
				}
				return is_array($array) ? $array : array();
			}
		}
	}
}