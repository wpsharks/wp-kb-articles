<?php
/**
 * Article Footer
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\footer'))
	{
		/**
		 * Article Footer
		 *
		 * @since 150113 First documented version.
		 */
		class footer extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Filters the content.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $content The content markup.
			 *
			 * @return string The `$content` w/ possible footer.
			 */
			public function filter($content)
			{
				$post    = $GLOBALS['post'];
				$content = (string)$content;

				if(!$post || !is_singular($this->plugin->post_type))
					return $content; // Not applicable.

				$template_vars = get_defined_vars();
				$template      = new template('site/articles/footer.php');

				return $content.$template->parse($template_vars);
			}
		}
	}
}