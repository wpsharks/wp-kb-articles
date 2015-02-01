<?php
/**
 * Post Utilities
 *
 * @since 150113 First documented version.
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
		 * @since 150113 First documented version.
		 */
		class utils_post extends abs_base
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
			 * Gets article popularity.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return integer Article popularity.
			 */
			public function get_popularity($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return 0; // Not possible.

				return (integer)get_post_meta($post_id, __NAMESPACE__.'_popularity', TRUE);
			}

			/**
			 * Updates article popularity.
			 *
			 * @since 150113 First documented version.
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
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return boolean `TRUE` if the vote is allowed in.
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

			/**
			 * Record article stats.
			 *
			 * @since 150201 Adding trending/popular.
			 *
			 * @param integer $post_id WordPress post ID.
			 */
			public function record_stats($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(!($ip = $this->plugin->utils_ip->current()) || $ip === 'unknown')
					return; // Not possible.

				$ymd_time  = strtotime(date('Y-m-d'));
				$transient = $this->plugin->transient_prefix.md5(__METHOD__.$post_id.$ymd_time.$ip);
				if(!($already_visited_today = (boolean)get_transient($transient)))
					set_transient($transient, time(), DAY_IN_SECONDS);

				$sql = // Update views and visits for this article.
					"INSERT INTO `".esc_sql($this->plugin->utils_db->prefix().'stats')."`".
					" (`post_id`, `ymd_time`, `views`, `visits`) VALUES('".esc_sql($post_id)."', '".esc_sql($ymd_time)."', '1', '1')".
					" ON DUPLICATE KEY UPDATE `views` = `views` + 1, `visits` = `visits` + ".($already_visited_today ? 0 : 1);
				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Filters author links that lead to article listings.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string  $link The URL/link that WordPress has.
			 * @param integer $author_id The author ID.
			 * @param string  $author_slug The author slug.
			 *
			 * @return string The filtered author link; w/ possible alterations.
			 */
			public function author_link_filter($link, $author_id, $author_slug)
			{
				if(!$this->plugin->options['sc_articles_list_index_post_id'])
					return $link; // Not applicable.

				if(is_admin()) // Not in the admin area.
					return $link; // Not applicable.

				if(!is_singular()) // Not singular?
					return $link; // Not applicable.

				if(empty($GLOBALS['post']) || $GLOBALS['post']->post_type !== $this->plugin->post_type)
					return $link; // Not applicable.

				$link        = trim((string)$link);
				$author_id   = (integer)$author_id;
				$author_slug = trim((string)$author_slug);

				if(!$author_slug) // Might be empty; WP core bug in filter.
					if(($author = get_userdata($author_id)) && !empty($author->user_nicename))
						$author_slug = $author->user_nicename;

				return ($link = $this->plugin->utils_url->sc_list('index', array('author' => $author_slug), TRUE));
			}

			/**
			 * Filters term links that lead to article listings.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string    $link The URL/link that WordPress has.
			 * @param \stdClass $term The term object associated w/ this link.
			 * @param string    $taxonomy The taxonomy that we are dealing with.
			 *
			 * @return string The filtered term link; w/ possible alterations.
			 */
			public function term_link_filter($link, \stdClass $term, $taxonomy)
			{
				if(!$this->plugin->options['sc_articles_list_index_post_id'])
					return $link; // Not applicable.

				if(is_admin()) // Not in the admin area.
					return $link; // Not applicable.

				$link     = trim((string)$link);
				$taxonomy = trim((string)$taxonomy);

				if($taxonomy === $this->plugin->post_type.'_category')
					return ($link = $this->plugin->utils_url->sc_list('index', array('category' => $term->slug), TRUE));

				if($taxonomy === $this->plugin->post_type.'_tag')
					return ($link = $this->plugin->utils_url->sc_list('index', array('tag' => $term->slug), TRUE));

				return $link; // Not applicable.
			}
		}
	}
}