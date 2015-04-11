<?php
/**
 * Index Handler
 *
 * @since 150410 Improving searches.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\index'))
	{
		/**
		 * Index Handler
		 *
		 * @since 150410 Improving searches.
		 */
		class index extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150410 Improving searches.
			 */
			public function __construct()
			{
				parent::__construct();
			}

			/**
			 * Rebuild entire index.
			 *
			 * @since 150410 Improving searches.
			 */
			public function rebuild()
			{
				$sql = // Delete any/all existing index rows.
					"TRUNCATE TABLE". // Truncate; i.e., reset `AUTO_INCREMENT`.
					" `".esc_sql($this->plugin->utils_db->prefix().'index')."`";
				$this->plugin->utils_db->wp->query($sql);

				$post_tags_sql_frag = // Sub-select that acquires post tag names.
					"SELECT GROUP_CONCAT(`terms`.`name` SEPARATOR ', ')". // Comma-delimited tag names.

					" FROM `".esc_sql($this->plugin->utils_db->wp->terms)."` AS `terms`".
					" INNER JOIN `".esc_sql($this->plugin->utils_db->wp->term_taxonomy)."` AS `term_taxonomy` ON `term_taxonomy`.`term_id` = `terms`.`term_id`".
					" INNER JOIN `".esc_sql($this->plugin->utils_db->wp->term_relationships)."` AS `term_relationships` ON `term_relationships`.`term_taxonomy_id` = `term_taxonomy`.`term_taxonomy_id`".

					" WHERE 1=1". // Initialize where clause.
					" AND `term_taxonomy`.`taxonomy` = '".esc_sql($this->plugin->post_type.'_tag')."'".
					" AND `term_relationships`.`object_id` = `post_id`";

				$sql = // Insertion syntax; w/ built-in sub-selects.
					"INSERT INTO `".esc_sql($this->plugin->utils_db->prefix().'index')."`".
					" (`post_id`, `post_title`, `post_tags`, `post_content`)".

					" SELECT". // Insert selection.

					" `posts`.`ID` AS `post_id`,".
					" `posts`.`post_title` AS `post_title`,".
					" (".$post_tags_sql_frag.") AS `post_tags`,".
					" `posts`.`post_content` AS `post_content`".

					" FROM `".esc_sql($this->plugin->utils_db->wp->posts)."` AS `posts`".

					" WHERE 1=1". // Initialize where clause.
					" AND `posts`.`post_type` = '".esc_sql($this->plugin->post_type)."'".
					" AND `posts`.`post_status` NOT IN('auto-draft')";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Synchronizes index w/ posts table.
			 *
			 * @since 150410 Improving searches.
			 *
			 * @param integer $post_id WordPress post ID.
			 * @param string  $action One of `save` or `delete`.
			 */
			public function sync($post_id, $action)
			{
				if(!($post_id = (integer)$post_id))
					return; // Not possible.

				if(!($post = get_post($post_id)))
					return; // Not possible.

				if($post->post_type !== $this->plugin->post_type)
					return; // Not applicable.

				if(!($action = trim(strtolower((string)$action))))
					return; // Not applicable.

				switch($action) // Sync in what way exactly?
				{
					case 'delete': // Deleting an article.

						$this->plugin->utils_db->wp->delete(
							$this->plugin->utils_db->prefix().'index',
							array('post_id' => $post->ID)
						);
						break; // Break switch handler.

					case 'save': // New article. Or, updating an existing article.

						$post_tags_sql_frag = // Sub-select that acquires post tag names.
							"SELECT GROUP_CONCAT(`terms`.`name` SEPARATOR ', ')". // Comma-delimited tag names.

							" FROM `".esc_sql($this->plugin->utils_db->wp->terms)."` AS `terms`".
							" INNER JOIN `".esc_sql($this->plugin->utils_db->wp->term_taxonomy)."` AS `term_taxonomy` ON `term_taxonomy`.`term_id` = `terms`.`term_id`".
							" INNER JOIN `".esc_sql($this->plugin->utils_db->wp->term_relationships)."` AS `term_relationships` ON `term_relationships`.`term_taxonomy_id` = `term_taxonomy`.`term_taxonomy_id`".

							" WHERE 1=1". // Initialize where clause.
							" AND `term_taxonomy`.`taxonomy` = '".esc_sql($this->plugin->post_type.'_tag')."'".
							" AND `term_relationships`.`object_id` = `post_id`";

						$sql = // Insert|replace syntax; w/ built-in sub-selects.
							"REPLACE INTO `".esc_sql($this->plugin->utils_db->prefix().'index')."`".
							" (`post_id`, `post_title`, `post_tags`, `post_content`)".

							" SELECT". // Insert selection.

							" `posts`.`ID` AS `post_id`,".
							" `posts`.`post_title` AS `post_title`,".
							" (".$post_tags_sql_frag.") AS `post_tags`,".
							" `posts`.`post_content` AS `post_content`".

							" FROM `".esc_sql($this->plugin->utils_db->wp->posts)."` AS `posts`".

							" WHERE 1=1". // Initialize where clause.
							" AND `posts`.`ID` = '".esc_sql($post->ID)."'".
							" AND `posts`.`post_type` = '".esc_sql($this->plugin->post_type)."'".
							" AND `posts`.`post_status` NOT IN('auto-draft')";

						$this->plugin->utils_db->wp->query($sql);

						break; // Break switch handler.
				}
			}
		}
	}
}
