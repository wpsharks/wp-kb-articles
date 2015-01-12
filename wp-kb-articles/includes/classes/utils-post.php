<?php
/**
 * Post Utilities
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_post'))
	{
		/**
		 * Post Utilities
		 *
		 * @since 141111 First documented version.
		 */
		class utils_post extends abs_base
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
			 * Gets article popularity.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return string Article popularity.
			 */
			public function get_popularity($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return ''; // Not possible.

				return (integer)get_post_meta($post_id, __NAMESPACE__.'_popularity', TRUE);
			}

			/**
			 * Updates article popularity.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 * @param integer $by e.g. `+1` or `-1`, etc.
			 */
			public function update_popularity($post_id, $by)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				$popularity = $this->get_popularity($post_id) + (integer)$by;

				update_post_meta($post_id, __NAMESPACE__.'_popularity', $popularity);
			}

			/**
			 * Cast article popularity vote.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return boolean `TRUE` if the vote is allowed in.
			 *
			 * @throws \exception If unable to set transient data entry.
			 */
			public function cast_popularity_vote($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return FALSE; // Not possible.

				if(!($ip = $this->plugin->utils_ip->current()) || $ip === 'unknown')
					return FALSE; // Not possible.

				$transient = $this->plugin->transient_prefix.md5(__METHOD__.$post_id.$ip);
				if(($already_voted_today = get_transient($transient)))
					return FALSE; // Not possible.

				set_transient($transient, time(), DAY_IN_SECONDS);
				$this->update_popularity($post_id, 1);

				return TRUE; // The vote was cast :-)
			}
		}
	}
}