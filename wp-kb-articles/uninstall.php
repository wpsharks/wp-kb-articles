<?php
/**
 * Uninstaller
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
if(!defined('WPINC'))
	exit('Do NOT access this file directly.');

$GLOBALS['wp_php_rv'] = '5.3'; // Minimum version.
if(require(dirname(__FILE__).'/submodules/wp-php-rv/wp-php-rv.php'))
	require_once dirname(__FILE__).'/uninstall.inc.php';
