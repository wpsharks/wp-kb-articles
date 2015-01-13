<?php
/**
 * GitHub Processor
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\github_processor'))
	{
		/**
		 * GitHub Processor
		 *
		 * @since 150113 First documented version.
		 */
		class github_processor extends abs_base
		{
			/**
			 * @var boolean A CRON job?
			 *
			 * @since 150113 First documented version.
			 */
			protected $is_cron;

			/**
			 * @var integer Start time.
			 *
			 * @since 150113 First documented version.
			 */
			protected $start_time;

			/**
			 * @var integer Max time (in seconds).
			 *
			 * @since 150113 First documented version.
			 */
			protected $max_time;

			/**
			 * @var integer Delay (in milliseconds).
			 *
			 * @since 150113 First documented version.
			 */
			protected $delay;

			/**
			 * @var integer Max entries to process.
			 *
			 * @since 150113 First documented version.
			 */
			protected $max_limit;

			/**
			 * @var array Files being processed in the tree.
			 *
			 * @since 150113 First documented version.
			 */
			protected $files;

			/**
			 * @var integer Total files.
			 *
			 * @since 150113 First documented version.
			 */
			protected $total_files;

			/**
			 * @var integer Processed file counter.
			 *
			 * @since 150113 First documented version.
			 */
			protected $processed_file_counter;

			/**
			 * @var github_api GitHub API instance.
			 *
			 * @since 150113 First documented version.
			 */
			protected $github_api;

			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param boolean      $is_cron Is this a CRON job?
			 *    Defaults to a `TRUE` value. If calling directly pass `FALSE`.
			 *
			 * @param integer|null $max_time Max time (in seconds).
			 *
			 *    This cannot be less than `10` seconds.
			 *    This cannot be greater than `300` seconds.
			 *
			 *    * A default value is taken from the plugin options.
			 *
			 * @param integer|null $delay Delay (in milliseconds).
			 *
			 *    This cannot be less than `0` milliseconds.
			 *    This (converted to seconds) cannot be greater than `$max_time` - `5`.
			 *
			 *    * A default value is taken from the plugin options.
			 *
			 * @param integer|null $max_limit Max files to process.
			 *
			 *    This cannot be less than `1`.
			 *    This cannot be greater than `1000` (filterable).
			 *
			 *    * A default value is taken from the plugin options.
			 */
			public function __construct($is_cron = TRUE, $max_time = NULL, $delay = NULL, $max_limit = NULL)
			{
				parent::__construct();

				$this->is_cron = (boolean)$is_cron;

				$this->start_time = time(); // Start time.

				if(isset($max_time)) // Argument is set?
					$this->max_time = (integer)$max_time; // This takes precedence.
				else $this->max_time = (integer)$this->plugin->options['github_processor_max_time'];

				if($this->max_time < 10) $this->max_time = 10;
				if($this->max_time > 300) $this->max_time = 300;

				if(isset($delay)) // Argument is set?
					$this->delay = (integer)$delay; // This takes precedence.
				else $this->delay = (integer)$this->plugin->options['github_processor_delay'];

				if($this->delay < 0) $this->delay = 0;
				if($this->delay && $this->delay / 1000 > $this->max_time - 5)
					$this->delay = 250; // Cannot be greater than max time - 5 seconds.

				if(isset($max_limit)) // Argument is set?
					$this->max_limit = (integer)$max_limit; // This takes precedence.
				else $this->max_limit = (integer)$this->plugin->options['github_processor_max_limit'];

				if($this->max_limit < 1) $this->max_limit = 1;
				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->max_limit > $upper_max_limit) $this->max_limit = $upper_max_limit;

				$this->files                  = array(); // Initialize.
				$this->total_files            = 0; // Initialize; zero for now.
				$this->processed_file_counter = 0; // Initialize; zero for now.
				$this->github_api             = NULL; // Initialize.

				$this->maybe_prep_cron_job();
				$this->maybe_process();
			}

			/**
			 * Prep CRON job.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_prep_cron_job()
			{
				if(!$this->is_cron)
					return; // Not applicable.

				ignore_user_abort(TRUE);

				@set_time_limit($this->max_time); // Max time only (first).
				// Doing this first in case the times below exceed an upper limit.
				// i.e. hosts may prevent this from being set higher than `$max_time`.

				// The following may not work, but we can try :-)
				if($this->delay) // Allow some extra time for the delay?
					@set_time_limit(min(300, ceil($this->max_time + ($this->delay / 1000) + 30)));
				else @set_time_limit(min(300, $this->max_time + 30));
			}

			/**
			 * GitHub processor.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_process()
			{
				if(!$this->plugin->options['github_processing_enable'])
					return; // Disabled currently.

				if(!$this->plugin->options['github_mirror_owner'])
					return; // Not possible.

				if(!$this->plugin->options['github_mirror_repo'])
					return; // Not possible.

				if(!$this->plugin->options['github_mirror_branch'])
					return; // Not possible.

				if(!$this->plugin->options['github_mirror_api_key'])
					if(!$this->plugin->options['github_mirror_username'] || !$this->plugin->options['github_mirror_password'])
						return; // possible.

				if(!$this->plugin->options['github_mirror_author'])
					return; // Not possible.

				$this->github_api = new github_api(
					array(
						'owner'    => $this->plugin->options['github_mirror_owner'],
						'repo'     => $this->plugin->options['github_mirror_repo'],

						'branch'   => $this->plugin->options['github_mirror_branch'],

						'username' => $this->plugin->options['github_mirror_username'],
						'password' => $this->plugin->options['github_mirror_password'],
						'api_key'  => $this->plugin->options['github_mirror_api_key'],
					));
				if(!($this->files = $this->github_api->retrieve_articles()))
					return; // Nothing to do.

				$this->total_files = count($this->files);

				foreach($this->files as $_path => $_file)
				{
					$this->maybe_process_file($_path, $_file);

					if($this->processed_file_counter >= $this->max_limit)
						break; // Reached limit; all done for now.

					if($this->processed_file_counter >= $this->total_files)
						break; // Processed every single file in the tree?

					if($this->is_out_of_time() || $this->is_delay_out_of_time())
						break; // Out of time now; or after a possible delay.
				}
				unset($_path, $_file); // Housekeeping.
			}

			/**
			 * File processor; if applicable.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $path GitHub file path; relative to repo root.
			 * @param array  $file File data from GitHub API tree call.
			 *
			 * @throws \exception If invalid parameters are pass to this routine.
			 * @throws \exception If there is any failure to acquire a particular article.
			 */
			protected function maybe_process_file($path, array $file)
			{
				if(!($path = trim((string)$path))) // Must have path.
					throw new \exception(__('Missing path.', $this->plugin->text_domain));

				if(empty($file['sha'])) // Must have this too.
					throw new \exception(__('Missing SHA1.', $this->plugin->text_domain));

				$post_id = $this->plugin->utils_github->path_post_id($path);

				if(!$post_id) // Article does not exist yet?
				{
					if(!($article = $this->github_api->retrieve_article($file['sha'])))
						throw new \exception(__('Article retrieval failure.', $this->plugin->text_domain));

					$github_mirror = new github_mirror(
						array_merge($article['headers'], array(
							'path' => $path,
							'sha'  => $file['sha'],
							'body' => $article['body'],
						)));
					$this->processed_file_counter++; // Bump the counter.
				}
				else if($this->plugin->utils_github->get_sha($post_id) !== $file['sha'])
				{
					if(!($article = $this->github_api->retrieve_article($file['sha'])))
						throw new \exception(__('Article retrieval failure.', $this->plugin->text_domain));

					$github_mirror = new github_mirror(
						array_merge($article['headers'], array(
							'path' => $path,
							'sha'  => $file['sha'],
							'body' => $article['body'],
						)));
					$this->processed_file_counter++; // Bump the counter.
				}
			}

			/**
			 * Out of time yet?
			 *
			 * @since 150113 First documented version.
			 *
			 * @return boolean TRUE if out of time.
			 */
			protected function is_out_of_time()
			{
				if((time() - $this->start_time) >= ($this->max_time - 5))
					return TRUE; // Out of time.

				return FALSE; // Let's keep mailing!
			}

			/**
			 * Out of time after a possible delay?
			 *
			 * @since 150113 First documented version.
			 *
			 * @return boolean TRUE if out of time.
			 */
			protected function is_delay_out_of_time()
			{
				if(!$this->delay) // No delay?
					return FALSE; // Nope; nothing to do here.

				if($this->processed_file_counter >= $this->total_files)
					return FALSE; // No delay on last blob.

				usleep($this->delay * 1000); // Delay.

				return $this->is_out_of_time();
			}
		}
	}
}