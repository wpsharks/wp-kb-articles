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
			 * Output footer content.
			 *
			 * @since 150113 First documented version.
			 */
			public function content()
			{
				$post = $GLOBALS['post'];

				if(!$post || $post->post_type !== $this->plugin->post_type)
					return ''; // Not applicable.

				if(!is_singular()) // Singulars articles only.
					return ''; // Not applicable.

				$template_vars = get_defined_vars();
				$template      = new template('site/articles/footer.php');

				return $template->parse($template_vars);
			}
		}
	}
}