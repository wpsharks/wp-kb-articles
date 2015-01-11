<?php
/**
 * Front Styles
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\front_styles'))
	{
		/**
		 * Front Styles
		 *
		 * @since 141111 First documented version.
		 */
		class front_styles extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_enqueue_list_styles();
			}

			/**
			 * Enqueue front-side styles for articles list.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_enqueue_list_styles()
			{
				if(!is_singular() || empty($GLOBALS['post']))
					return; // Not a post/page.

				if(stripos($GLOBALS['post']->post_content, '[kb_articles_list') === FALSE)
					return; // Current singular post/page does not contain the shortcode.

				wp_enqueue_style(__NAMESPACE__.'_list', $this->plugin->utils_url->to('/client-s/css/list.min.css'), array(), $this->plugin->version, 'all');
			}
		}
	}
}