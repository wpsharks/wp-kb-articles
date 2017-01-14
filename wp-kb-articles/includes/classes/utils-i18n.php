<?php
/**
 * i18n Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly.');

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_i18n'))
	{
		/**
		 * i18n Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_i18n extends abs_base
		{

		}
	}
}