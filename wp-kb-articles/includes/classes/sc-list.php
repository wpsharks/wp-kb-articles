<?php
/**
 * Shortcode for Articles List
 *
 * @since 150107 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sc_list'))
	{
		/**
		 * Shortcode for Articles List
		 *
		 * @since 150107 First documented version.
		 */
		class sc_list extends abs_base
		{
			/**
			 * Shortcode attributes.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var \stdClass Shortcode attributes.
			 */
			protected $attr;

			/**
			 * Shortcode content string.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Shortcode content string.
			 */
			protected $content;

			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array  $attr Shortcode attributes.
			 * @param string $content Shortcode content string.
			 */
			public function __construct(array $attr, $content = '')
			{
				parent::__construct();

				$default_attr = array(
					'page'     => '1',
					'per_page' => '25', // Cannot exceed max limit.

					'orderby'  => 'popularity:DESC,comment_count:DESC,date:DESC',

					'author'   => '', // Satisfy all; comma-delimited slugs/IDs.
					'category' => '', // Satisfy all; comma-delimited slugs.
					'tag'      => '', // Satisfy all; comma-delimited slugs.
					'q'        => '', // Search query.
				);
				$attr         = array_merge($default_attr, $attr);
				$attr         = array_intersect_key($attr, $default_attr);

				$this->attr    = (object)$attr;
				$this->content = (string)$content;

				foreach($this->attr as $_prop => &$_value)
					if(!empty($_REQUEST[$this->plugin->qv_prefix.$_prop]) && in_array($_prop, array('page', 'orderby', 'category', 'tag'), TRUE))
						$_value = trim(stripslashes((string)$_REQUEST[$this->plugin->qv_prefix.$_prop]));
				unset($_prop, $_value); // Housekeeping.

				foreach($this->attr as $_prop => &$_value)
					if(in_array($_prop, array('page', 'per_page'), TRUE))
						$_value = (integer)trim($_value);
					else $_value = trim((string)$_value);
				unset($_prop, $_value); // Housekeeping.

				if($this->attr->page < 1)
					$this->attr->page = 1;

				if($this->attr->per_page < 1)
					$this->attr->per_page = 1;

				$upper_max_limit = (integer)apply_filters(__CLASS__.'_upper_max_limit', 1000);
				if($this->attr->per_page > $upper_max_limit)
					$this->attr->per_page = $upper_max_limit;

				$_orderbys           = preg_split('/,+/', $this->attr->orderby, NULL, PREG_SPLIT_NO_EMPTY);
				$_orderbys           = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($_orderbys));
				$this->attr->orderby = array(); // Reset; convert to an associative array.
				foreach($_orderbys as $_orderby) // Validate each orderby.
				{
					if(!$_orderby || strpos($_orderby, ':', 1) === FALSE)
						continue; // Invalid syntax.

					list($_orderby, $_order) = explode(':', $_orderby, 2);
					$_order = strtoupper($_order); // e.g. `ASC`, `DESC`.

					if(!in_array($_orderby, array('popularity', 'comment_count', 'date'), TRUE))
						continue; // Invalid syntax; i.e. invalid orderby column.

					if(!in_array($_order, array('ASC', 'DESC'), TRUE))
						continue; // Invalid syntax; i.e. invalid order.

					if($_orderby === 'popularity')
						$_orderby = 'meta_value_num';
					$this->attr->orderby[$_orderby] = $_order;
				}
				unset($_orderbys, $_orderby, $_order); // Housekeeping.

				if(!$this->attr->orderby) // Use default orderby values?
					$this->attr->orderby = array('meta_value_num' => 'DESC', 'comment_count' => 'DESC', 'date' => 'DESC');

				$_authors           = preg_split('/,+/', $this->attr->author, NULL, PREG_SPLIT_NO_EMPTY);
				$_authors           = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($_authors));
				$this->attr->author = array(); // Reset; convert to an array of author IDs.
				foreach($_authors as $_author) // Validate each author.
				{
					if(!is_numeric($_author)) // Convert username to ID.
					{
						$_author = \WP_User::get_data_by('login', $_author);
						$_author = $_author ? $_author->ID : 0;
					}
					if(($_author = (integer)$_author))
						$this->attr->author[] = $_author;
				}
				unset($_authors, $_author); // Housekeeping.

				$this->attr->category = preg_split('/,+/', $this->attr->category, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->category = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->category));

				$this->attr->tag = preg_split('/,+/', $this->attr->tag, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->tag = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->tag));
			}

			/**
			 * Shortcode parser.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return string Parsed shortcode; i.e. HTML markup.
			 */
			public function parse()
			{
				$attr          = $this->attr;
				$query         = $this->query();
				$template_vars = get_defined_vars();
				$template      = new template('site/articles/list.php');

				return $template->parse($template_vars);
			}

			/**
			 * Performs the query.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return \WP_Query The query class instance.
			 */
			protected function query()
			{
				$args = array(
					'post_type'           => $this->plugin->post_type,

					'posts_per_page'      => $this->attr->per_page,
					'paged'               => $this->attr->page,

					'orderby'             => $this->attr->orderby,

					'meta_key'            => __NAMESPACE__.'_popularity',
					'meta_query'          => array(
						array(
							'key'     => __NAMESPACE__.'_popularity',
							'compare' => 'EXISTS', 'type' => 'SIGNED',
						),
					),
					'ignore_sticky_posts' => FALSE, // Allow stickies.
				);
				if($this->attr->author)
				{
					$args['author__in'] = $this->attr->author;
				}
				if($this->attr->category)
				{
					if(empty($args['tax_query']['relation']))
						$args['tax_query']['relation'] = 'AND';

					$args['tax_query'][] = array(
						'taxonomy'         => $this->plugin->post_type.'_category',
						'terms'            => $this->attr->category,
						'field'            => 'slug',
						'include_children' => TRUE,
						'operator'         => 'AND',
					);
				}
				if($this->attr->tag)
				{
					if(empty($args['tax_query']['relation']))
						$args['tax_query']['relation'] = 'AND';

					$args['tax_query'][] = array(
						'taxonomy' => $this->plugin->post_type.'_tag',
						'terms'    => $this->attr->tag,
						'field'    => 'slug',
						'operator' => 'AND',
					);
				}
				if($this->attr->q)
					$args['s'] = $this->attr->q;

				return new \WP_Query($args);
			}
		}
	}
}