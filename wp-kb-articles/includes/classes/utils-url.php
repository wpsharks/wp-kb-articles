<?php
/**
 * URL Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_url'))
	{
		/**
		 * URL Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_url extends abs_base
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
			 * Current scheme; lowercase.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current scheme; lowercase.
			 */
			public function current_scheme()
			{
				if(!is_null($scheme = &$this->static_key(__FUNCTION__)))
					return $scheme; // Cached this already.

				return ($scheme = is_ssl() ? 'https' : 'http');
			}

			/**
			 * Current front scheme; lowercase.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current front scheme; lowercase.
			 *
			 * @note This will return `https://` only if we are NOT in the admin area.
			 *    Also, see {@link \home_url()} for some other considerations.
			 */
			public function current_front_scheme()
			{
				if(!is_null($scheme = &$this->static_key(__FUNCTION__)))
					return $scheme; // Cached this already.

				return ($scheme = (string)parse_url(home_url(), PHP_URL_SCHEME));
			}

			/**
			 * Sets URL scheme.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link \set_url_scheme()} in WordPress for further details.
			 *
			 * @return string URL w/ the proper scheme.
			 *
			 * @note Regarding the special `front` scheme:
			 *    {@link home_url()} establishes the standards we use.
			 *
			 *    It is NOT necessary to use `front` in most scenarios,
			 *       but there are some edge cases where it has a purpose.
			 *
			 *    e.g. building a URL that leads {@to()} a plugin file (while {@link is_admin()});
			 *       but where the URL is intended for display on the front-end of the site.
			 *
			 * @uses set_url_scheme()
			 * @uses current_front_scheme()
			 */
			public function set_scheme($url = '', $scheme = NULL)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				if($scheme === 'front') // Front-side?
					$scheme = $this->current_front_scheme();

				return set_url_scheme($url, $scheme);
			}

			/**
			 * Current host name; lowercase.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param boolean $no_port No port number? Defaults to `FALSE`.
			 *
			 * @note Some hosts include a port number in `$_SERVER['HTTP_HOST']`.
			 *    That SHOULD be left intact for URL generation in almost every scenario.
			 *    However, in a few other edge cases it may be desirable to exclude the port number.
			 *    e.g. if the purpose of obtaining the host is to use it for email generation, or in a slug, etc.
			 *
			 * @return string Current host name; lowercase.
			 */
			public function current_host($no_port = FALSE)
			{
				if(!is_null($host = &$this->static_key(__FUNCTION__, $no_port)))
					return $host; // Cached this already.

				$host = strtolower((string)$_SERVER['HTTP_HOST']);

				if($no_port) // Remove possible port number?
					$host = preg_replace('/\:[0-9]+$/', '', $host);

				return $host; // Current host (cached).
			}

			/**
			 * Current `host[/path]`; w/ multisite compat.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current `host/path`; w/ multisite compat.
			 *
			 * @note We don't cache this, since a blog can get changed at runtime.
			 */
			public function current_host_path()
			{
				if(is_multisite()) // Multisite network?
				{
					global $current_blog; // Current MS blog.

					$host = rtrim($current_blog->domain, '/');
					$path = trim($current_blog->path, '/');

					return strtolower(trim($host.'/'.$path, '/'));
				}
				return strtolower($this->plugin->utils_url->current_host(TRUE));
			}

			/**
			 * Current base/root host name; w/ multisite compat.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current base/root host name; w/ multisite compat.
			 *
			 * @note We don't cache this, since a blog can get changed at runtime.
			 */
			public function current_host_base()
			{
				if(is_multisite()) // Multisite network?
				{
					global $current_blog; // Current MS blog.

					$host = strtolower(rtrim($current_blog->domain, '/'));
					if(defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL)
						return $host; // Intentional sub-domain.
				}
				else $host = $this->current_host(); // Standard WP installs.

				if(substr_count($host, '.') > 1) // Reduce to base/root host name.
				{
					$_parts = explode('.', $host); // e.g. `www.example.com` becomes `example.com`.
					$host   = $_parts[count($_parts) - 2].'.'.$_parts[count($_parts) - 1];
					unset($_parts); // Housekeeping.
				}
				return strtolower($host); // Base/root host name.
			}

			/**
			 * Current URI; with a leading `/`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current URI; with a leading `/`.
			 */
			public function current_uri()
			{
				if(!is_null($uri = &$this->static_key(__FUNCTION__)))
					return $uri; // Cached this already.

				return ($uri = '/'.ltrim((string)$_SERVER['REQUEST_URI'], '/'));
			}

			/**
			 * Current URI/path; with a leading `/`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current URI/path; with a leading `/`.
			 */
			public function current_path()
			{
				if(!is_null($path = &$this->static_key(__FUNCTION__)))
					return $path; // Cached this already.

				return ($path = '/'.ltrim((string)parse_url($this->current_uri(), PHP_URL_PATH), '/'));
			}

			/**
			 * Current path info; e.g. `index.php/path/info/`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current path info; e.g. `index.php/path/info/`.
			 */
			public function current_path_info()
			{
				if(!is_null($path_info = &$this->static_key(__FUNCTION__)))
					return $path_info; // Cached this already.

				$path_info = isset($_SERVER['PATH_INFO']) ? (string)$_SERVER['PATH_INFO'] : '';
				if(strpos($path_info, '?') !== FALSE) list($path_info) = explode('?', $path_info);
				$path_info = $this->plugin->utils_string->trim($path_info, '', '/');

				return ($path_info = str_replace('%', '%25', $path_info));
			}

			/**
			 * Current URL; i.e. scheme.host.URI put together.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Current URL; i.e. scheme.host.URI put together.
			 */
			public function current($scheme = NULL)
			{
				if(!is_null($url = &$this->static_key(__FUNCTION__, $scheme)))
					return $url; // Cached this already.

				$url = '//'.$this->current_host().$this->current_uri();

				return ($url = $this->set_scheme($url, $scheme));
			}

			/**
			 * URL without a query string.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL without a query string.
			 */
			public function no_query($url = '', $scheme = NULL)
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$url = strpos($url, '?') !== FALSE ? (string)strstr($url, '?', TRUE) : $url;

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with `_wpnonce`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with `_wpnonce`.
			 */
			public function nonce($nonce_action = __NAMESPACE__, $url = '', $scheme = 'admin')
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				$args = array('_wpnonce' => wp_create_nonce($nonce_action));
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with only a `page` var (if applicable).
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with only a `page` var (if applicable).
			 *
			 * @note In this plugin we do allow `post_type` together on
			 *    plugin menu pages. The plugin rides on the menu for its post type.
			 */
			public function page_only($page = '', $url = '', $scheme = 'admin')
			{
				$page      = trim((string)$page);
				$post_type = ''; // Initialize.

				if(!($url = trim((string)$url)))
					$url = $this->current();

				$query = (string)parse_url($url, PHP_URL_QUERY);
				wp_parse_str($query, $query_vars);
				$url = $this->no_query($url);

				if(!$page && !empty($query_vars['page']))
					$page = trim((string)$query_vars['page']);

				if(!$page && !empty($_REQUEST['page']))
					$page = trim(stripslashes((string)$_REQUEST['page']));

				if($page && strpos($page, __NAMESPACE__) === 0)
				{
					if(!$post_type && !empty($query_vars['post_type']))
						$post_type = trim((string)$query_vars['post_type']);

					if(!$post_type && !empty($_REQUEST['post_type']))
						$post_type = trim(stripslashes((string)$_REQUEST['post_type']));
				}
				$args = $page ? array('page' => $page) : array();
				if($args && $page && $post_type && $post_type === $this->plugin->post_type)
					$args = array_merge($args, compact('post_type'));
				$url = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * URL with only a `page` var (if applicable) and `_wpnonce`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $page A specific page value (optional).
			 *    If empty, we use `page` from the URL; else current `page`.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the current URL.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL with only a `page` var (if applicable) and `_wpnonce`.
			 */
			public function page_nonce_only($page = '', $nonce_action = __NAMESPACE__, $url = '', $scheme = 'admin')
			{
				$url = $this->page_only($page, $url);

				return $this->nonce($nonce_action, $url, $scheme);
			}

			/**
			 * Main menu page URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Main menu page URL.
			 */
			public function main_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/edit.php?post_type='.urlencode($this->plugin->post_type));

				return $this->page_only(__NAMESPACE__, $url, $scheme);
			}

			/**
			 * Main menu page URL; w/ `_wpnonce`.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Main menu page URL; w/ `_wpnonce`.
			 */
			public function main_menu_page_nonce_only($nonce_action = __NAMESPACE__, $scheme = 'admin')
			{
				$url = $this->main_menu_page_only();

				return $this->nonce($nonce_action, $url, $scheme);
			}

			/**
			 * Import/export menu page URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Import/export menu page URL.
			 */
			public function import_export_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/edit.php?post_type='.urlencode($this->plugin->post_type));

				return $this->page_only(__NAMESPACE__.'_import_export', $url, $scheme);
			}

			/**
			 * Site templates menu page URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Site templates menu page URL.
			 */
			public function site_templates_menu_page_only($scheme = 'admin')
			{
				$url = admin_url('/edit.php?post_type='.urlencode($this->plugin->post_type));

				return $this->page_only(__NAMESPACE__.'_site_templates', $url, $scheme);
			}

			/**
			 * Options updated URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Options updated URL.
			 */
			public function options_updated($scheme = 'admin')
			{
				return $this->page_only('', '', $scheme);
			}

			/**
			 * Restore default options URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Restore default options URL.
			 */
			public function restore_default_options($scheme = 'admin')
			{
				$url  = $this->main_menu_page_nonce_only(__NAMESPACE__, $scheme);
				$args = array(__NAMESPACE__ => array('restore_default_options' => '1'));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Options restored URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Options restored URL.
			 */
			public function default_options_restored($scheme = 'admin')
			{
				return $this->main_menu_page_only($scheme);
			}

			/**
			 * Restore default options URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $type New type/mode to use.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Restore default options URL.
			 */
			public function set_template_type($type, $scheme = 'admin')
			{
				$type = trim(strtolower((string)$type));
				$url  = $this->page_nonce_only('', __NAMESPACE__, '', $scheme);
				$args = array(__NAMESPACE__ => array('set_template_type' => $type));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Template type updated URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Template type updated URL.
			 */
			public function template_type_updated($scheme = 'admin')
			{
				return $this->page_only('', '', $scheme);
			}

			/**
			 * Pro preview URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $url The input URL to work from (optional).
			 *    If empty, defaults to the main menu page.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Pro preview URL.
			 */
			public function pro_preview($url = '', $scheme = 'admin')
			{
				if(!($url = trim((string)$url)))
					$url = $this->main_menu_page_only();

				$args = array(__NAMESPACE__.'_pro_preview' => '1');
				$url  = add_query_arg(urlencode_deep($args), $url);

				return $this->set_scheme($url, $scheme);
			}

			/**
			 * Notice dimissal URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $notice_key The notice key to dismiss.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Notice dimissal URL.
			 */
			public function dismiss_notice($notice_key, $scheme = 'admin')
			{
				$notice_key = trim((string)$notice_key);

				$url  = $this->nonce(__NAMESPACE__, '', $scheme);
				$args = array(__NAMESPACE__ => array('dismiss_notice' => compact('notice_key')));

				return add_query_arg(urlencode_deep($args), $url);
			}

			/**
			 * Notice dimissed URL.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional . Defaults to `admin`.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Notice dimissed URL.
			 */
			public function notice_dismissed($scheme = 'admin')
			{
				$url = $this->current($scheme);

				return remove_query_arg(__NAMESPACE__, $url);
			}

			/**
			 * Product page URL; normally at WebSharks™.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Product page URL; normally at WebSharks™.
			 */
			public function product_page($scheme = NULL)
			{
				if(!empty($this->plugin->product_url))
					$url = $this->plugin->product_url; // Provided by plugin class?
				else $url = 'http://www.websharks-inc.com/product/'.urlencode($this->plugin->slug).'/';

				return isset($scheme) ? $this->set_scheme($url, $scheme) : $url;
			}

			/**
			 * Subscribe page URL; normally at WebSharks™.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Subscribe page URL; normally at WebSharks™.
			 */
			public function subscribe_page($scheme = NULL)
			{
				if(!empty($this->plugin->subscribe_url))
					$url = $this->plugin->subscribe_url; // Provided by plugin class?
				else $url = 'http://www.websharks-inc.com/r/'.urlencode($this->plugin->slug).'-subscribe/';

				return isset($scheme) ? $this->set_scheme($url, $scheme) : $url;
			}

			/**
			 * URL to a plugin file.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $file Optional file path; relative to plugin directory.
			 *
			 * @param string|null $scheme Optional. Defaults to a `NULL` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string URL to plugin directory; or to the specified `$file` if applicable.
			 */
			public function to($file = '', $scheme = NULL)
			{
				if(is_null($plugin_dir_url = &$this->static_key(__FUNCTION__, 'plugin_dir_url')))
					$plugin_dir_url = rtrim(plugin_dir_url($this->plugin->file), '/');

				return $this->set_scheme($plugin_dir_url.(string)$file, $scheme);
			}

			/**
			 * Checks for a valid `_wpnonce` value.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $nonce_action A specific nonce action.
			 *    Defaults to `__NAMESPACE__`.
			 *
			 * @param string $url A specific URL to check?
			 *    Defaults to the current URL; i.e. current `$_REQUEST`.
			 *
			 * @return boolean TRUE if it has a valid `_wpnonce`.
			 */
			public function has_valid_nonce($nonce_action = __NAMESPACE__, $url = '')
			{
				if(($url = trim((string)$url)))
					wp_parse_str((string)@parse_url($url, PHP_URL_QUERY), $_r);
				else $_r = stripslashes_deep($_REQUEST);

				if(!empty($_r['_wpnonce']) && wp_verify_nonce($_r['_wpnonce'], $nonce_action))
					return TRUE; // Valid `_wpnonce` value.

				return FALSE; // Unauthenticated; failure.
			}

			/**
			 * Shortcode list w/ possible query vars.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string      $url A specific URL to work from?
			 *    Defaults to the current URL. Pass `index` to force Page w/ shortcode.
			 *    Note: using `index` when `sc_articles_list_index_post_id` is empty
			 *    will result in failure; i.e. this will return an empty string.
			 *
			 * @param array       $qvs Associative array of query vars.
			 *    These should not be prefixed in any way.
			 *
			 * @param boolean     $exclude_current_qvs Defaults to a `FALSE` value.
			 *    Set this to `TRUE` to exclude current query var values.
			 *
			 * @param string|null $scheme Optional. Defaults to `front` value.
			 *    See {@link set_scheme()} method for further details.
			 *
			 * @return string Link to the shortcode list w/ possible query vars.
			 *    Note: using `index` when `sc_articles_list_index_post_id` is empty
			 *    will result in failure; i.e. this will return an empty string.
			 */
			public function sc_list($url = '', array $qvs = array(), $exclude_current_qvs = FALSE, $scheme = 'front')
			{
				if(!($url = trim((string)$url)))
					$url = $this->current();

				else if($url === 'index' && (integer)$this->plugin->options['sc_articles_list_index_post_id'])
					$url = get_permalink((integer)$this->plugin->options['sc_articles_list_index_post_id']);

				if(!$url || $url === 'index')
					return ''; // Not possible.

				$url_has_query = strpos($url, '?') !== FALSE;

				foreach($this->plugin->qv_keys as $_qv)
				{
					if($url_has_query) // Remove existing.
						$url = remove_query_arg($this->plugin->qv_prefix.$_qv, $url);
					$url = preg_replace('/\/'.preg_quote($this->plugin->rewrite_prefix.$_qv, '/').'\/[^\/]+/', '', $url);
				}
				if(!$exclude_current_qvs) foreach($this->plugin->qv_keys as $_qv)
				{
					if(isset($qvs[$_qv])) continue; // Set already.

					if(!empty($_REQUEST[$this->plugin->qv_prefix.$_qv]))
						$qvs[$_qv] = trim(stripslashes((string)$_REQUEST[$this->plugin->qv_prefix.$_qv]));

					else if(($_qv_value = get_query_var($this->plugin->qv_prefix.$_qv)))
						$qvs[$_qv] = (string)$_qv_value; // Current value.
				}
				unset($_qv, $_qv_value); // Housekeeping.

				if($qvs && ($url_has_query || !get_option('permalink_structure')))
				{
					$args = array(); // Initialize.
					foreach($qvs as $_key => $_value)
						if($_key !== 'page' || $_value > 1)
							$args[$this->plugin->qv_prefix.$_key] = $_value;
					unset($_key, $_value); // Housekeeping.

					$url = add_query_arg(urlencode_deep($args), $url);
				}
				else if($qvs) // Rewrite (default behavior).
				{
					$url = rtrim($url, '/');
					foreach($qvs as $_key => $_value)
						if($_key !== 'page' || $_value > 1)
							$url .= '/'.urlencode($this->plugin->rewrite_prefix.$_key).'/'.urlencode($_value);
					unset($_key, $_value); // Housekeeping.

					$url = user_trailingslashit($url);
				}
				return $this->set_scheme($url, $scheme);
			}
		}
	}
}