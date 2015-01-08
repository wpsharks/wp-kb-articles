<?php
/**
 * GitHub Mirror
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\github_mirror'))
	{
		/**
		 * GitHub Mirror
		 *
		 * @since 141111 First documented version.
		 */
		class github_mirror extends abs_base
		{
			/**
			 * @var string GitHub sha1 hash.
			 */
			protected $sha;

			/**
			 * @var string GitHub file path.
			 */
			protected $path;

			/**
			 * @var integer Post ID.
			 */
			protected $post_id;

			/**
			 * @var boolean It's new?
			 */
			protected $is_new;

			/**
			 * @var string Slug.
			 */
			protected $slug;

			/**
			 * @var string Title.
			 */
			protected $title;

			/**
			 * @var array Categories.
			 */
			protected $categories;

			/**
			 * @var array Tags.
			 */
			protected $tags;

			/**
			 * @var integer Author.
			 */
			protected $author;

			/**
			 * @var string Status.
			 */
			protected $status;

			/**
			 * @var string Pub date.
			 */
			protected $pubdate;

			/**
			 * @var string Body.
			 */
			protected $body;

			/**
			 * @var string Excerpt.
			 */
			protected $excerpt;

			/**
			 * @var string Comment status.
			 */
			protected $comment_status;

			/**
			 * @var string Ping status.
			 */
			protected $ping_status;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $args Arguments to constructor.
			 *
			 * @throws \exception If the `sha` or `path` args are empty.
			 *
			 * @TODO A specific user needs to be set as the current user before this is called upon.
			 */
			public function __construct(array $args)
			{
				parent::__construct();

				$default_args = array(
					'sha'            => '', // SHA1 hash from GitHub.
					'path'           => '', // e.g. `my/article.md`.

					'slug'           => '', // e.g. `my-article`.
					'title'          => '', // e.g. My Article Title.

					'categories'     => '', // Comma-delimited list.
					'tags'           => '', // Comma-delimited list.

					'author'         => '', // `1`, or `johndoe` (ID or username).
					'status'         => '', // `draft`, `pending`, `publish`, `future`, etc.
					'pubdate'        => '', // `strtotime()` compatible.

					'body'           => '', // Article body content.
					'excerpt'        => '', // Article excerpt.

					'comment_status' => '', // `open` or `closed`.
					'ping_status'    => '', // `open` or `closed`.
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				if(!($this->sha = trim((string)$args['sha'])))
					throw new \exception(__('Missing sha.', $this->plugin->text_domain));

				if(!($this->path = trim((string)$args['path'])))
					throw new \exception(__('Missing path.', $this->plugin->text_domain));

				$this->slug  = trim((string)$args['slug']);
				$this->title = trim((string)$args['title']);

				$this->categories = trim((string)$args['categories']);
				$this->tags       = trim((string)$args['tags']);

				$this->author  = trim((string)$args['author']);
				$this->status  = trim((string)$args['status']);
				$this->pubdate = trim((string)$args['pubdate']);

				$this->body    = trim((string)$args['body']);
				$this->excerpt = trim((string)$args['excerpt']);

				$this->comment_status = trim((string)$args['comment_status']);
				$this->ping_status    = trim((string)$args['ping_status']);

				$this->normalize_props();

				wp_insert_post();
				wp_update_post();
			}

			protected function normalize_props()
			{
				$this->post_id = $this->plugin->utils_github->path_post_id($this->path);
				$this->is_new  = empty($this->post_id); // No post ID yet?

				if($this->is_new) // It's a new article not yet in the system?
				{
					if(!$this->slug) // Convert path to slug.
						$this->slug = $this->plugin->utils_github->path_to_slug($this->path);

					if(!$this->title) // Get title from the body.
						$this->title = $this->plugin->utils_github->body_title($this->body);

					if(!$this->author) // Use default author in this case.
						$this->author = (integer)$this->plugin->options['github_mirror_user_id'];

					if(!$this->status) // Default status.
						$this->status = 'pending'; // Pending review.

					if(!$this->pubdate) // Use the current time.
						$this->pubdate = time(); // Current UTC timestamp.

					if(!$this->comment_status) // Default comment status.
						$this->comment_status = get_option('default_comment_status');

					if(!$this->ping_status) // Default ping status.
						$this->ping_status = get_option('default_ping_status');
				}
				$this->slug = strtolower($this->slug); // Force lowercase.

				$this->categories = preg_split('/,+/', $this->categories, NULL, PREG_SPLIT_NO_EMPTY);
				$this->categories = $this->plugin->utils_string->trim_deep($this->categories);
				$this->categories = $this->plugin->utils_array->remove_emptys($this->categories);

				$this->tags = preg_split('/,+/', $this->tags, NULL, PREG_SPLIT_NO_EMPTY);
				$this->tags = $this->plugin->utils_string->trim_deep($this->tags);
				$this->tags = $this->plugin->utils_array->remove_emptys($this->tags);

				if($this->author && !is_numeric($this->author))
					if(($_author_user = \WP_User::get_data_by('login', $this->author)))
						$this->author = $_author_user->ID; // User ID.
				unset($_author_user); // A little housekeeping.

				$this->author  = (integer)$this->author;
				$this->status  = strtolower($this->status);
				$this->pubdate = (integer)$this->pubdate;

				if($this->post_id)
				{
					$post = get_post($this->post_id); // @TODO
				}
			}
		}
	}
}