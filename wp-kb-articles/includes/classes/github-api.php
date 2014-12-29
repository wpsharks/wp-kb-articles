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

			public function retrieve_posts($get_body = FALSE)
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
						$body = $this->retrieve_body($post['sha']);

						if(!$body) return FALSE; // TODO error handling

						if(!strpos(trim($body), '---'))
							$post['body'] = $body;
						else
						{
							$startsAt = strpos($body, '---') + strlen('---');
							$endsAt   = strpos($body, '---', $startsAt);

							$yaml = substr($body, $startsAt, $endsAt - $startsAt);
							$body = substr($body, $endsAt - $startsAt);

							unset($startsAt, $endsAt);

							$post['body'] = $body;

							if($yaml && strlen($yaml))
							{
								$lines = explode("\n", $yaml);

								foreach($lines as $line)
								{
									list($name, $value) = explode(':', $line, 2);
									$post['headers'][trim($name)] = trim($value);
								}
							}
						}
					}

					$posts[$post['path']] = $post;
				}

				return $posts;
			}

			public function retrieve_post($a)
			{
				$data = $this->retrieve_body($a);

				if(!$data) return FALSE;

				return $data;
			}

			/* === Base GitHub Retrieval === */

			public function retrieve_body($a)
			{
				$is_sha = (bool)preg_match('/^[0-9a-f]{40}$/i', $a);

				if($is_sha)
				{
					$blob = $this->retrieve_blob($a);

					if(!$blob) return FALSE;
					if($blob['encoding'] === 'base64') return base64_decode($blob['content']);
					return $blob['content'];
				}
				else// It's a path
					return $this->retrieve_file($a);
			}

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
				$url = 'api.github.com/repos/%1$s/%2$s/git/blobs/%3$s';
				$url = sprintf($url, $this->owner, $this->repo, $sha);

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
			 * @param string $user
			 * @param string $pass
			 */
			public function authenticate($user, $pass)
			{
				$this->username = strtolower(trim((string)$user));
				$this->password = trim((string)$pass);
			}

			/**
			 * HTTP Request Method
			 */
			private function get_response($url, $args = array())
			{
				// Allow for overriding of defaults
				$_args = array('headers' => array(), 'user-agent' => apply_filters(__NAMESPACE__.'_github_api_user_agent', 'WP KB Articles for '.get_site_url()));
				$args  = array_merge_recursive($_args, $args);
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