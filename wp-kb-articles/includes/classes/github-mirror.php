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
			 * @var array Args.
			 */
			protected $args;

			/**
			 * @var string GitHub sha1 hash.
			 */
			protected $sha;

			/**
			 * @var string GitHub file path.
			 */
			protected $path;

			/**
			 * @var \WP_Post|null Post.
			 */
			protected $post;

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
				$this->args   = $args; // Set arguments property.

				$this->normalize_props(); // Normalize all properties.

				$this->mirror(); // Mirror headers/body.
			}

			/**
			 * Normalizes all class properties.
			 *
			 * @since 141111 First documented version.
			 *
			 * @throws \exception If the `sha` or `path` args are empty.
			 */
			protected function normalize_props()
			{
				# Collect string values.

				if(!($this->sha = trim((string)$this->args['sha'])))
					throw new \exception(__('Missing sha.', $this->plugin->text_domain));

				if(!($this->path = trim((string)$this->args['path'])))
					throw new \exception(__('Missing path.', $this->plugin->text_domain));

				$this->slug  = trim((string)$this->args['slug']);
				$this->title = trim((string)$this->args['title']);

				$this->categories = trim((string)$this->args['categories']);
				$this->tags       = trim((string)$this->args['tags']);

				$this->author  = trim((string)$this->args['author']);
				$this->status  = trim((string)$this->args['status']);
				$this->pubdate = trim((string)$this->args['pubdate']);

				$this->body    = trim((string)$this->args['body']);
				$this->excerpt = trim((string)$this->args['excerpt']);

				$this->comment_status = trim((string)$this->args['comment_status']);
				$this->ping_status    = trim((string)$this->args['ping_status']);

				# Convert to post ID, if possible.

				if(($_post_id = $this->plugin->utils_github->path_post_id($this->path)))
					$this->post = get_post($_post_id); // Get the existing article.
				unset($_post_id); // Housekeeping.

				# Determine if post is new; i.e. there's no existing post?

				$this->is_new = empty($this->post); // No post ID yet?

				# Handle new KB articles; i.e. new posts.

				if($this->is_new) // It's a new KB article; i.e. post?
				{
					if(!$this->slug) // Convert path to slug in this case.
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
				# Normalize all properties.

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

				if($this->body && preg_match('/\.md$/i', $this->path))
					$this->body = $this->plugin->utils_string->markdown($this->body);

				$this->comment_status = strtolower($this->comment_status);
				$this->ping_status    = strtolower($this->ping_status);
			}

			/**
			 * Mirrors article/post.
			 *
			 * @since 141111 First documented version.
			 */
			protected function mirror()
			{
				if($this->is_new)
					$this->insert();
				else $this->update();
			}

			/**
			 * Inserts a new article/post.
			 *
			 * @since 141111 First documented version.
			 *
			 * @throws \exception If unable to insert article.
			 */
			protected function insert()
			{
				$data = array(
					'' => '',
				);
				if(!($ID = wp_insert_post($data)) || !($this->post = get_post($ID)))
					throw new \exception(__('Insertion failure.', $this->plugin->text_domain));

				$this->maybe_update_terms(); // Updates terms; i.e. categories/tags.
			}

			/**
			 * Updating existing article/post.
			 *
			 * @since 141111 First documented version.
			 *
			 * @throws \exception If unable to update article.
			 */
			protected function update()
			{
				$data = array(
					'ID' => $this->post->ID,
				);
				if(!wp_update_post($data)) // Update failure?
					throw new \exception(__('Update failure.', $this->plugin->text_domain));

				$this->maybe_update_terms(); // Updates terms; i.e. categories/tags.
			}

			/**
			 * Updates terms; i.e. categories/tags.
			 *
			 * @since 141111 First documented version.
			 *
			 * @throws \exception If unable to update terms.
			 */
			protected function maybe_update_terms()
			{
				if($this->categories) // Updating categories in this case?
					if(is_wp_error(wp_set_object_terms($this->post->ID, $this->categories, $this->plugin->post_type.'_category')))
						throw new \exception(__('Category update failure.', $this->plugin->text_domain));

				if($this->tags) // Updating tags in this case?
					if(is_wp_error(wp_set_object_terms($this->post->ID, $this->tags, $this->plugin->post_type.'_tag')))
						throw new \exception(__('Tag update failure.', $this->plugin->text_domain));
			}
		}
	}
}