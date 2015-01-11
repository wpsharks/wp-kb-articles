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
				$this->maybe_enqueue_footer_styles();
			}

			/**
			 * Enqueue front-side styles for articles list.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_enqueue_list_styles()
			{
				if(empty($GLOBALS['post']) || !is_singular())
					return; // Not a post/page.

				if(stripos($GLOBALS['post']->post_content, '[kb_articles_list') === FALSE)
					return; // Current singular post/page does not contain the shortcode.

				wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), array(), NULL, 'all');
				wp_enqueue_style(__NAMESPACE__.'_list', $this->plugin->utils_url->to('/client-s/css/list.min.css'), array('font-awesome'), $this->plugin->version, 'all');
			}

			/**
			 * Enqueue front-side styles for article footer.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_enqueue_footer_styles()
			{
				if(empty($GLOBALS['post']) || !is_singular())
					return; // Not a post/page.

				if($GLOBALS['post']->post_type !== $this->plugin->post_type)
					return; // It's not a KB article post type.

				wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), array(), NULL, 'all');
				wp_enqueue_style(__NAMESPACE__.'_list', $this->plugin->utils_url->to('/client-s/css/footer.min.css'), array('font-awesome'), $this->plugin->version, 'all');
			}
		}
	}
}