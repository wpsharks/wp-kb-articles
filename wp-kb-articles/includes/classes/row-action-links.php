<?php
/**
 * Article Row Actions
 *
 * @since 150302 Adding post row actions.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly.');

	if(!class_exists('\\'.__NAMESPACE__.'\\row_action_links'))
	{
		/**
		 * Article Row Actions
		 *
		 * @since 150302 Adding post row actions.
		 */
		class row_action_links extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150302 Adding post row actions.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Filters the row actions.
			 *
			 * @since 150302 Adding post row actions.
			 *
			 * @param array    $actions Current actions.
			 * @param \WP_Post $post Current post.
			 *
			 * @return array New row actions after having been filtered.
			 */
			public function filter(array $actions, \WP_Post $post)
			{
				if($post->post_type !== $this->plugin->post_type)
					return $actions; // Not applicable.

				return $actions; // Nothing at this time.
			}
		}
	}
}