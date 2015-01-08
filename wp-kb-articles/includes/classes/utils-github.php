<?php
/**
 * GitHub Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_github'))
	{
		/**
		 * GitHub Utilities @TODO
		 *
		 * @since 141111 First documented version.
		 */
		class utils_github extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Converts a repo path into a WP slug.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $path GitHub repo path to a file.
			 */
			public function path_to_slug($path)
			{
				$path = trim((string)$path);
			}

			/**
			 * Builds a title based on the content of an article body.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $body Article body.
			 */
			public function body_title($body)
			{
				$body = trim((string)$body);
			}

			/**
			 * Converts a repo path to a post ID.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $path GitHub repo path to a file.
			 */
			public function path_post_id($path)
			{
				$path = trim((string)$path);
			}
		}
	}
}