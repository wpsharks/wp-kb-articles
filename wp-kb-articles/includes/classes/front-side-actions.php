<?php
/**
 * Front-Side Actions
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\front_side_actions'))
	{
		/**
		 * Front-Side Actions
		 *
		 * @since 150113 First documented version.
		 */
		class front_side_actions extends abs_base
		{
			/**
			 * @var array Valid actions.
			 *
			 * @since 150113 First documented version.
			 */
			protected $valid_actions;

			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->valid_actions = array(
					'sc_list_via_ajax',
					'cast_popularity_vote_via_ajax',
					'record_stats_via_ajax',
				);
				$this->maybe_handle();
			}

			/**
			 * Action handler.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_handle()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Not applicable.

				foreach((array)$_REQUEST[__NAMESPACE__] as $_action => $_request_args)
					if($_action && in_array($_action, $this->valid_actions, TRUE))
						$this->{$_action}($this->plugin->utils_string->trim_strip_deep($_request_args));
				unset($_action, $_request_args); // Housekeeping.
			}

			/**
			 * Shortcode list via AJAX.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function sc_list_via_ajax($request_args)
			{
				$this->plugin->utils_env->doing_ajax(TRUE);

				$attr    = (string)$request_args;
				$attr    = $this->plugin->utils_enc->xdecrypt($attr);
				$attr    = (array)maybe_unserialize($attr);
				$sc_list = new sc_list($attr, '');

				status_header(200); // Return response.
				header('Content-Type: text/html; charset=UTF-8');
				exit($sc_list->parse().'<!-- </html> -->');
			}

			/**
			 * Cast popularity vote via AJAX.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function cast_popularity_vote_via_ajax($request_args)
			{
				define('DONOTCACHEPAGE', TRUE);
				define('ZENCACHE_ALLOWED', FALSE);

				$this->plugin->utils_env->doing_ajax(TRUE);
				$post_id = (integer)$request_args;

				status_header(200); // Return response.
				nocache_headers(); // Disallow browser cache.
				header('Content-Type: text/plain; charset=UTF-8');
				exit((string)(integer)$this->plugin->utils_post->cast_popularity_vote($post_id));
			}

			/**
			 * Record stats via AJAX.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $request_args Input argument(s).
			 */
			protected function record_stats_via_ajax($request_args)
			{
				define('DONOTCACHEPAGE', TRUE);
				define('ZENCACHE_ALLOWED', FALSE);

				$this->plugin->utils_env->doing_ajax(TRUE);
				$post_id = (integer)$request_args;

				status_header(200); // Return response.
				nocache_headers(); // Disallow browser cache.
				header('Content-Type: text/plain; charset=UTF-8');
				exit((string)(integer)$this->plugin->utils_post->record_stats($post_id));
			}
		}
	}
}