<?php
/**
 * GitHub Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_github'))
	{
		/**
		 * GitHub Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_github extends abs_base
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
			 * Converts a repo path into a WP slug.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $path GitHub repo path to a file.
			 *
			 * @return string Slugified path.
			 */
			public function path_to_slug($path)
			{
				$path = trim((string)$path);

				$slug = preg_replace('/\.[^.]*$/', '', $path);
				$slug = preg_replace('/[^a-z0-9]/i', '-', $slug);
				$slug = trim($slug, '-');

				return substr($slug, 0, 200);
			}

			/**
			 * Builds a title based on the content of an article body.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $body Article body.
			 *
			 * @return string Title from body; else `Untitled`.
			 */
			public function body_title($body)
			{
				$body = trim((string)$body);

				foreach(explode("\n", $body) as $_line)
					if(strpos($_line, '#') === 0 && ($_title = trim($_line, " \r\n\t\0\x0B".'#')))
						return $_title; // Markdown title line.
				unset($_line, $_title); // Housekeeping.

				return $this->plugin->utils_string->clip($body);
			}

			/**
			 * Converts a repo path to a post ID.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $path GitHub repo path to a file.
			 *
			 * @return integer WordPress Post ID.
			 */
			public function path_post_id($path)
			{
				$path = trim((string)$path);

				$sql = "SELECT `post_id` FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".
				       " WHERE `meta_key` = '".esc_sql(__NAMESPACE__.'_github_path')."'".
				       " AND `meta_value` = '".esc_sql($path)."'".
				       " ORDER BY `post_id` DESC LIMIT 1";

				return (integer)$this->plugin->utils_db->wp->get_var($sql);
			}

			/**
			 * Gets repo path for an article.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return string Repo path for the article.
			 */
			public function get_path($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return ''; // Not possible.

				return trim((string)get_post_meta($post_id, __NAMESPACE__.'_github_path', TRUE));
			}

			/**
			 * Updates repo path for an article.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 * @param string  $path The repo file path.
			 */
			public function update_path($post_id, $path)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(!($path = trim((string)$path)))
					return; // Not possible.

				update_post_meta($post_id, __NAMESPACE__.'_github_path', $path);
			}

			/**
			 * Gets SHA1 hash for an article.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 *
			 * @return string SHA1 hash for the article.
			 */
			public function get_sha($post_id)
			{
				if(!($post_id = (integer)$post_id))
					return ''; // Not possible.

				return trim((string)get_post_meta($post_id, __NAMESPACE__.'_github_sha', TRUE));
			}

			/**
			 * Updates SHA1 hash for an article.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $post_id WordPress post ID.
			 * @param string  $sha Most recent SHA1 hash.
			 */
			public function update_sha($post_id, $sha)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(!($sha = trim((string)$sha)))
					return; // Not possible.

				update_post_meta($post_id, __NAMESPACE__.'_github_sha', $sha);
			}
		}
	}
}