<?php
/**
 * GitHub API Class
 *
 * @since 141228 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\github_api'))
	{
		/**
		 * GitHub Processor
		 *
		 * @since 141111 First documented version.
		 */
		class github_api/* extends abs_base*/
		{

			private $owner, $repo, $branch = 'HEAD';

			private $apiKey, $username, $password;

			public $allow_https = TRUE;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				//parent::__construct();
			}

			/* === Main Retrieval === */

			public function retrieve_files($get_body = FALSE)
			{
				$tree  = $this->retrieve_tree();
				$posts = array();

				if(!$tree) return FALSE; // Error

				foreach($tree['tree'] as $blob)
				{
					if($blob['type'] !== 'blob') continue;
					if(!preg_match('/\.md$/i', $blob['path'])) continue;

					$post = array('headers' => array(), 'body' => '', 'sha' => $blob['sha'], 'url' => $blob['url'], 'path' => $blob['path']);

					if($get_body)
					{
						$blob = $this->retrieve_blob($post['sha']);

						if(!$blob) return FALSE; // TODO error handling

						$body = base64_decode($blob['body']);

						if(!strpos(trim($body), '---'))
						{
							$post['body']         = $body;
						}
						else
						{
							$yaml = yaml_parse($body, 0);
						}
					}

					$posts[$post['path']] = $post;
				}

				return $posts;
			}

			/* === Base GitHub Retrieval === */

			public function retrieve_tree()
			{
				$url = 'api.github.com/repos/%1$s/%2$s/git/trees/%3$s?recursive=1';
				$url = sprintf($url, $this->owner, $this->repo, $this->branch);

				$response = $this->get_response($url);

				if($response) return json_decode($response['body'], TRUE);
				else return FALSE;
			}

			public function retrieve_blob($sha)
			{
				$url = 'api.github.com/repos/%1$s/%2$s/git/trees/%3$s?recursive=1';
				$url = sprintf($url, $this->owner, $this->repo, $this->branch);

				$response = $this->get_response($url);

				if($response) return json_decode($response['body'], TRUE);
				else return FALSE;
			}

			public function retrieve_file($file)
			{
				$url = 'raw.githubusercontent.com/%1$s/%2$s/%3$s/%4$s';
				$url = sprintf($url, $this->owner, $this->repo, $this->branch, $file);

				$response = $this->get_response($url);

				if($response) return $response['body'];
				else return FALSE;
			}

			/* === Default Info === */

			public function set_owner($owner)
			{
				$this->owner = strtolower(trim($owner));
			}

			public function set_repo($repo)
			{
				$this->repo = strtolower(trim($repo));
			}

			public function set_branch($branch)
			{
				if(is_string($branch) && strlen($branch))
					$this->branch = strtolower(trim($branch));
				else $this->branch = 'HEAD';
			}

			/**
			 * Authentication
			 *
			 * @param        $a
			 * @param string $b
			 */
			public function authenticate($a, $b = FALSE)
			{
				if($b === FALSE)
					$this->apiKey = trim($a);

				else
				{
					$this->username = strtolower(trim($a));
					$this->password = strtolower(trim($b));
				}
			}

			/**
			 * HTTP Request Method
			 */
			private function get_response($url, $args = array())
			{
				// Allow for overriding of defaults
				$_args = array('headers' => array(), 'user-agent' => apply_filters(__NAMESPACE__.'_github_api_user_agent', 'WP KB Articles for '.get_site_url()));
				$args  = array_merge($_args, $args);
				unset($_args);

				// If Authorization done via GitHub API Key
				if($this->apiKey) $args['headers'][] = 'Authorization: token '.$this->apiKey;

				// For Authorization via Username + Password
				if(strlen($this->username) && strlen($this->password)) $before = $this->username.':'.$this->password.'@';
				else $before = '';

				// Create request URL
				if($this->allow_https) $url = 'https://'.$before.$url;
				else $url = 'http://'.$before.$url;

				unset($before);

				// Class relies on WP_Http
				$request       = wp_remote_request($url, $args);
				$body          = wp_remote_retrieve_body($request);
				$headers       = wp_remote_retrieve_headers($request);
				$response_code = wp_remote_retrieve_response_code($request);

				unset($url, $args);

				if($response_code !== 302 && $response_code !== 200) return FALSE; // Error

				// array('request' => $request, 'body' => $body, 'headers' => $headers, 'response_code' => $response_code);
				return get_defined_vars();
			}
		}
	}
}