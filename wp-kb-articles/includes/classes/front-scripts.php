<?php
/**
 * Front Scripts
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\front_scripts'))
	{
		/**
		 * Front Scripts
		 *
		 * @since 141111 First documented version.
		 */
		class front_scripts extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_enqueue_list_scripts();
			}

			/**
			 * Enqueue front-side scripts for articles list.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_enqueue_list_scripts()
			{
				if(!is_singular() || empty($GLOBALS['post']))
					return; // Not a post/page.

				if(stripos($GLOBALS['post']->post_content, '[kb_articles_list') === FALSE)
					return; // Current singular post/page does not contain the shortcode.

				wp_enqueue_script('jquery'); // Need jQuery.
				wp_enqueue_script(__NAMESPACE__.'_list', $this->plugin->utils_url->to('/client-s/js/list.min.js'), array('jquery'), $this->plugin->version, TRUE);

				wp_localize_script(__NAMESPACE__.'_list', __NAMESPACE__.'_list_vars', array(
					'pluginUrl'    => rtrim($this->utils_url->to('/'), '/'),
					'ajaxEndpoint' => rtrim(home_url('/'), '/'),
				));
				wp_localize_script(__NAMESPACE__.'_list', __NAMESPACE__.'_list_i18n', array());
			}
		}
	}
}