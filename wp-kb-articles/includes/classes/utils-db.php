<?php
/**
 * DB Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_db'))
	{
		/**
		 * DB Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_db extends abs_base
		{
			/**
			 * @var \wpdb WP DB class reference.
			 *
			 * @since 150113 First documented version.
			 */
			public $wp;

			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->wp = $GLOBALS['wpdb'];
			}

			/**
			 * Current DB prefix for this plugin.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Current DB table prefix.
			 */
			public function prefix()
			{
				return $this->wp->prefix.$this->plugin->post_type.'_';
			}

			/**
			 * Typify result properties deeply.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $value Any value can be typified deeply.
			 *
			 * @return mixed Typified value.
			 */
			public function typify_deep($value)
			{
				if(is_array($value) || is_object($value))
				{
					foreach($value as $_key => &$_value)
					{
						if(is_array($_value) || is_object($_value))
							$_value = $this->typify_deep($_value);

						else if($this->is_integer_key($_key))
							$_value = (integer)$_value;

						else if($this->is_float_key($_key))
							$_value = (float)$_value;

						else $_value = (string)$_value;
					}
					unset($_key, $_value); // Housekeeping.
				}
				return $value; // Typified deeply.
			}

			/**
			 * Should an array/object key contain an integer value?
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $key The input key to check.
			 *
			 * @return boolean TRUE if the key should contain an integer value.
			 */
			public function is_integer_key($key)
			{
				if(!$key || !is_string($key))
					return FALSE;

				$key = strtolower($key);

				$integer_keys             = array(
					'id',
					'time',
					'count',
					'counter',
				);
				$preg_quoted_integer_keys = array_map(function ($key)
				{
					return preg_quote($key, '/'); #

				}, $integer_keys);

				if(preg_match('/(?:^|_)(?:'.implode('|', $preg_quoted_integer_keys).')(?:_before)?$/i', $key))
					return TRUE; // e.g. `id`, `x_id`, `x_x_id`, `x_id_before`, `time_before`, `x_time_before`.

				return FALSE; // Default.
			}

			/**
			 * Should an array/object key contain a float value?
			 *
			 * @since 150113 First documented version.
			 *
			 * @param mixed $key The input key to check.
			 *
			 * @return boolean TRUE if the key should contain a float value.
			 */
			public function is_float_key($key)
			{
				return FALSE; // Default; no float keys at this time.
			}

			/**
			 * Check DB engine compat. w/ fulltext indexes.
			 *
			 * @since 150131 Adding statistics.
			 *
			 * @param string $sql Input SQL to check.
			 *
			 * @return string Output `$sql` w/ possible engine modification.
			 *    Only MySQL v5.6.4+ supports fulltext indexes with the InnoDB engine.
			 *    Otherwise, we use MyISAM for any table that includes a fulltext index.
			 *
			 * @note MySQL v5.6.4+ supports fulltext indexes w/ InnoDB.
			 *    See: <http://bit.ly/ZVeF42>
			 */
			public function fulltext_compat($sql)
			{
				if(!($sql = trim((string)$sql)))
					return $sql; // Empty.

				if(!preg_match('/^CREATE\s+TABLE\s+/i', $sql))
					return $sql; // Not applicable.

				if(!preg_match('/\bFULLTEXT\s+KEY\b/i', $sql))
					return $sql; // No fulltext index.

				if(!preg_match('/\bENGINE\=InnoDB\b/i', $sql))
					return $sql; // Not using InnoDB anyway.

				$mysql_version = $this->wp->db_version();
				if($mysql_version && version_compare($mysql_version, '5.6.4', '>='))
					return $sql; // MySQL v5.6.4+ supports fulltext indexes.

				return preg_replace('/\bENGINE\=InnoDB\b/i', 'ENGINE=MyISAM', $sql);
			}

			/**
			 * Post comment status translator.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer|string $status
			 *
			 *    One of the following:
			 *       - `0` (aka: ``, `closed`, `close`).
			 *       - `1` (aka: `opened`, `open`).
			 *
			 * @return string `open`, `closed`.
			 *
			 * @throws \exception If an unexpected status is encountered.
			 */
			public function post_comment_status__($status)
			{
				switch(trim(strtolower((string)$status)))
				{
					case '1':
					case 'open':
					case 'opened':
						return 'open';

					case '0':
					case '':
					case 'close':
					case 'closed':
						return 'closed';

					default: // Throw exception on anything else.
						throw new \exception(sprintf(__('Unexpected post comment status: `%1$s`.', $this->plugin->text_domain), $status));
				}
			}

			/**
			 * Pagination links start page.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param integer $current_page The current page number.
			 * @param integer $total_pages The total pages available.
			 * @param integer $max_links Max pagination links to display.
			 *
			 * @return integer The page number to begin pagination links from.
			 *
			 * @note This method has been tested; even against invalid figures.
			 *    It handles every scenario gracefully; even if invalid figures are given.
			 */
			public function pagination_links_start_page($current_page, $total_pages, $max_links)
			{
				$current_page = (integer)$current_page;
				$total_pages  = (integer)$total_pages;
				$max_links    = (integer)$max_links;

				$min_start_page = 1; // Obviously.
				$max_start_page = max($total_pages - ($max_links - 1), $min_start_page);
				$start_page     = max(min($current_page - floor($max_links / 2), $max_start_page), $min_start_page);

				return (integer)$start_page;
			}
		}
	}
}