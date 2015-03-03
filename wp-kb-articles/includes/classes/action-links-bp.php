<?php
/**
 * Article Actions Before Permalink
 *
 * @since 150302 Adding post actions.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\action_links_bp'))
	{
		/**
		 * Article Actions Before Permalink
		 *
		 * @since 150302 Adding post actions.
		 */
		class action_links_bp extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150302 Adding post actions.
			 *
			 * @param \WP_Post $post Current post.
			 */
			public function __construct(\WP_Post $post)
			{
				parent::__construct();

				$this->hook($post);
			}

			/**
			 * Hooks into actions.
			 *
			 * @since 150302 Adding post actions.
			 *
			 * @param \WP_Post $post Current post.
			 */
			public function hook(\WP_Post $post)
			{
				if($post->post_type !== $this->plugin->post_type)
					return; // Not applicable.

				// Nothing at this time.
			}
		}
	}
}