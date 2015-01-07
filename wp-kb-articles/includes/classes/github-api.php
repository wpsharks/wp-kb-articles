<?php
/**
 * GitHub API Class
 *
 * @since 150107 First documented version.
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
		 * GitHub API Class
		 *
		 * @since 150107 First documented version.
		 */
		class github_api
		{

			private $owner, $repo, $branch = 'HEAD';

			private $api_key, $username, $password;

			public $allow_https = TRUE;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * $param array $args Array of arguments specific to the GitHub integration about to be executed. Required.
			 */
			public function __construct(array $args)
			{
				$default_args = array(
					'owner'    => '',
					'repo'     => '',
					'branch'   => 'HEAD',
					'username' => '',
					'password' => '',
					'api_key'  => ''
				);

				$args = array_merge($default_args, $args);
				$args = array_intersect_key($args, $default_args);

				foreach($args as $_key => $_value)
				{
					if(empty($_value)) continue;

					switch($_key)
					{
						case 'password':
						case 'api_key':
							if(isset($args['username']) && !empty($args['username']))
							{
								$this->username = strtolower(trim((string)$args['username']));
								$this->password = trim((string)$_value);
							}
							break;
						case 'owner':
							$this->owner = strtolower(trim($_value));
							break;
						case 'repo':
							$this->repo = strtolower(trim($_value));
							break;
						case 'branch':
							$this->branch = trim($_value);
							break;
					}
				}
			}

			/* === Public Methods === */

			/**
			 * Retrieves an array of data for all `.MD` files within a repo
			 *
			 * @param bool $get_body If TRUE, this function will retrieve body contents for each `.md` file in the request
			 *
			 * @return array|false
			 */
			public function retrieve_articles($get_body = FALSE)
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

						$post = array_merge_recursive($post, $this->parse_article($body));
					}

					$posts[$post['path']] = $post;
				}

				return $posts;
			}

			/**
			 * Retrieves an associative array for information on a particular article, including the body
			 *
			 * @param string $a SHA1 key or path to file
			 *
			 * @return array|false
			 */
			public function retrieve_article($a)
			{
				$data = $this->retrieve_body($a);

				if(!$data) return FALSE;

				$post = array('sha' => sha1($data));

				return array_merge_recursive($post, $this->parse_article($data));
			}

			/* === Base GitHub Retrieval === */

			/**
			 * Wrapper function for retrieve_blob and retrieve_file based on $a
			 *
			 * @param string $a SHA1 key or path to file
			 *
			 * @return string|false
			 */
			protected function retrieve_body($a)
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

			/**
			 * Retrieves list of files (as in, directory list) recursively from GitHub repo
			 *
			 * @return array|false FALSE on error, else array of files from GitHub
			 */
			protected function retrieve_tree()
			{
				$url = 'api.github.com/repos/%1$s/%2$s/git/trees/%3$s?recursive=1';
				$url = sprintf($url, $this->owner, $this->repo, $this->branch);

				$response = $this->get_response($url);

				if($response) return json_decode($response['body'], TRUE);
				else return FALSE;
			}

			/**
			 * Retrieves UTF-8 encoded file from GitHub via SHA1 key
			 *
			 * @param string $sha SHA1 value to be retrieved from the GitHub repo
			 *
			 * @return string|false FALSE on error, else string body from GitHub
			 */
			protected function retrieve_blob($sha)
			{
				$url = 'api.github.com/repos/%1$s/%2$s/git/blobs/%3$s';
				$url = sprintf($url, $this->owner, $this->repo, $sha);

				$response = $this->get_response($url);

				if($response) return json_decode($response['body'], TRUE);
				else return FALSE;
			}

			/**
			 * Retrieves a UTF-8 encoded raw file from GitHub via path
			 *
			 * @param string $path The path to the file to be retrieved
			 *
			 * @return string|false FALSE on error, else string body from GitHub
			 */
			protected function retrieve_file($path)
			{
				$url = 'raw.githubusercontent.com/%1$s/%2$s/%3$s/%4$s';
				$url = sprintf($url, $this->owner, $this->repo, $this->branch, $path);

				$response = $this->get_response($url);

				if($response) return $response['body'];
				return FALSE;
			}

			/**
			 * Parses a KB article w/ possible YAML front matter.
			 *
			 * @param string $article Input article content to parse.
			 *
			 * @return array An array with two elements.
			 *    - `headers` An associative array of all YAML headers.
			 *    - `body` The body part of the article after YAML headers were parsed.
			 */
			protected function parse_article($article)
			{
				$parts = array(
					'headers' => array(),
					'body'    => '',
				);

				// Normalize line breaks. Use "\n" for all line breaks.
				$article = str_replace(array("\r\n", "\r"), "\n", $article);
				$article = trim($article);

				if(strpos($article, '---'."\n") !== 0)
				{
					$parts['body'] = $article;
					return $parts;
				}

				$article_parts = preg_split('/^\-{3}$/m', $article, 3);

				// If the article does NOT have three parts, it contains no YAML front matter.
				if(count($article_parts) !== 3)
				{
					$parts['body'] = $article;
					return $parts;
				}

				list(, $article_headers_part, $article_body_part) = $article_parts;

				foreach(explode("\n", trim($article_headers_part)) as $_line)
				{
					if(!($_line = trim($_line)))
						continue; // Skip over empty lines; i.e. with whitespace only.

					if(strpos($_line, ':', 1) !== FALSE)
					{
						list($_name, $_value) = explode(':', $_line, 2);
						$parts['headers'][strtolower(trim($_name))] = trim($_value);
					}
				}
				unset($_line, $_name, $_value); // Housekeeping.

				$parts['body'] = trim($article_body_part);

				return $parts;
			}

			/**
			 * Universal GitHub HTTP Request Method
			 *
			 * @param string $url The URL to request
			 * @param array  $args An associative array of arguments that can be used to overwrite the defaults used by the function
			 *
			 * @return array
			 */
			protected function get_response($url, $args = array())
			{
				// Allow for overriding of defaults
				$_args = array('headers' => array(), 'user-agent' => apply_filters(__NAMESPACE__.'_github_api_user_agent', 'WP KB Articles for '.get_site_url()));
				$args  = array_merge_recursive($_args, $args);
				unset($_args);

				// If Authorization done via GitHub API Key
				if($this->api_key) $args['headers'][] = 'Authorization: token '.$this->api_key;

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