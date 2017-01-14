<?php
/**
 * Front Scripts
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly.');

	if(!class_exists('\\'.__NAMESPACE__.'\\front_scripts'))
	{
		/**
		 * Front Scripts
		 *
		 * @since 150113 First documented version.
		 */
		class front_scripts extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				if(is_admin())
					return; // Not applicable.

				$this->maybe_enqueue_list_search_box_scripts();
				$this->maybe_enqueue_list_scripts();
				$this->maybe_enqueue_footer_scripts();
			}

			/**
			 * Enqueue front-side scripts for list search box.
			 *
			 * @since 150220 Enhancing search box.
			 */
			protected function maybe_enqueue_list_search_box_scripts()
			{
				if(empty($GLOBALS['post']) || !is_singular())
					return; // Not a post/page.

				if(stripos($GLOBALS['post']->post_content, '[kb_articles_list_search_box') === FALSE && !apply_filters(__METHOD__, false))
					return; // Current singular post/page does not contain the shortcode.

				wp_enqueue_script('jquery'); // Need jQuery.

				add_action('wp_footer', function ()
				{
					$template = new template('site/articles/list-search-box.js.php');
					echo $template->parse(); // Inline `<script></script>`.

				}, PHP_INT_MAX - 10); // After WP footer scripts!
			}

			/**
			 * Enqueue front-side scripts for articles list.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_enqueue_list_scripts()
			{
				if(empty($GLOBALS['post']) || !is_singular())
					return; // Not a post/page.

				if(stripos($GLOBALS['post']->post_content, '[kb_articles_list') === FALSE)
					return; // Current singular post/page does not contain the shortcode.

				wp_enqueue_script('jquery'); // Need jQuery.

				add_action('wp_footer', function ()
				{
					$template = new template('site/articles/list.js.php');
					echo $template->parse(); // Inline `<script></script>`.

				}, PHP_INT_MAX - 10); // After WP footer scripts!
			}

			/**
			 * Enqueue front-side scripts for article footer.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_enqueue_footer_scripts()
			{
				if(!is_singular($this->plugin->post_type))
					return; // Not a post/page.

				wp_enqueue_script('jquery'); // Need jQuery.

				add_action('wp_footer', function ()
				{
					$template = new template('site/articles/footer.js.php');
					echo $template->parse(); // Inline `<script></script>`.

				}, PHP_INT_MAX - 10); // After WP footer scripts!
			}
		}
	}
}
