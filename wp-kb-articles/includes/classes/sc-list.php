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
			 * Raw shortcode attributes.
			 *
			 * @since 150113 First documented version.
			 *
			 * @var array Raw shortcode attributes.
			 */
			protected $attr_;

			/**
			 * Shortcode attributes.
			 *
			 * @since 150113 First documented version.
			 *
			 * @var \stdClass Shortcode attributes.
			 */
			protected $attr;

			/**
			 * Shortcode content string.
			 *
			 * @since 150113 First documented version.
			 *
			 * @var string Shortcode content string.
			 */
			protected $content;

			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param array  $attr Shortcode attributes.
			 * @param string $content Shortcode content string.
			 */
			public function __construct(array $attr, $content = '')
			{
				parent::__construct();

				$default_attr = array(
					'page'           => '1', // Page number.
					'per_page'       => '25', // Cannot exceed max limit.

					'orderby'        => 'popularity:DESC,comment_count:DESC,date:DESC',

					'author'         => '', // Satisfy all; comma-delimited slugs/IDs.
					'category'       => '', // Satisfy all; comma-delimited slugs/IDs.
					'tab_categories' => '', // For tabs; comma-delimited slugs/IDs.
					'tag'            => '', // Satisfy all; comma-delimited slugs/IDs.
					'q'              => '', // Search query.

					'url'            => $this->plugin->utils_url->current(),
				);
				if(isset($attr['orderbys']) && !isset($attr['orderby']))
					$attr['orderby'] = $attr['orderbys'];

				if(isset($attr['authors']) && !isset($attr['author']))
					$attr['author'] = $attr['authors'];

				if(isset($attr['categories']) && !isset($attr['category']))
					$attr['category'] = $attr['categories'];

				if(isset($attr['tags']) && !isset($attr['tag']))
					$attr['tag'] = $attr['tags'];

				$attr = array_merge($default_attr, $attr);
				$attr = array_intersect_key($attr, $default_attr);

				$this->attr    = (object)$attr;
				$this->attr_   = $attr; // Originals.
				$this->content = (string)$content;

				foreach($this->attr as $_prop => &$_value) // e.g. `page`, `author`, etc.
					if(in_array($_prop, $this->plugin->qv_keys, TRUE) && ($_qv = get_query_var($this->plugin->qv_prefix.$_prop)))
						$_value = (string)$_qv; // e.g. `page`, `author`, etc.
				unset($_prop, $_value, $_qv); // Housekeeping.

				foreach($this->attr as $_prop => &$_value) // e.g. `page`, `author`, etc.
					if(!empty($_REQUEST[$this->plugin->qv_prefix.$_prop]) && in_array($_prop, $this->plugin->qv_keys, TRUE))
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

				$_orderbys            = preg_split('/,+/', $this->attr->orderby, NULL, PREG_SPLIT_NO_EMPTY);
				$_orderbys            = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($_orderbys));
				$this->attr->orderbys = $_orderbys; // Preserve the array of orderby clauses for templates.
				$this->attr->orderby  = array(); // Reset; convert to an associative array.
				foreach($_orderbys as $_orderby) // Validate each orderby.
				{
					if(strpos($_orderby, ':', 1) === FALSE)
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

				$this->attr->author = preg_split('/,+/', $this->attr->author, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->author = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->author));
				foreach($this->attr->author as $_key => &$_author) // Validate each author.
				{
					if(is_numeric($_author)) // Convert username to ID.
					{
						if(!($_author = (integer)$_author))
							unset($this->attr->author[$_key]);
						continue; // All done here.
					}
					$_author = \WP_User::get_data_by('login', $_author);
					if(!$_author || !($_author = $_author->ID))
						unset($this->attr->author[$_key]);
				}
				unset($_key, $_author); // Housekeeping.

				$this->attr->category = preg_split('/,+/', $this->attr->category, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->category = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->category));
				foreach($this->attr->category as $_key => &$_category) // Validate each category.
				{
					if(is_numeric($_category))
					{
						if(!($_category = (integer)$_category))
							unset($this->attr->category[$_key]);
						continue; // All done here.
					}
					$_term = get_term_by('slug', $_category, $this->plugin->post_type.'_category');
					if(!$_term || !($_category = (integer)$_term->term_id))
						unset($this->attr->category[$_key]);
				}
				unset($_key, $_category, $_term); // Housekeeping.

				$this->attr->tab_categories = preg_split('/,+/', $this->attr->tab_categories, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->tab_categories = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->tab_categories));
				foreach($this->attr->tab_categories as $_key => &$_category) // Validate each category.
				{
					if(is_numeric($_category))
					{
						if(!($_category = (integer)$_category))
							unset($this->attr->tab_categories[$_key]);
						continue; // All done here.
					}
					$_term = get_term_by('slug', $_category, $this->plugin->post_type.'_category');
					if(!$_term || !($_category = (integer)$_term->term_id))
						unset($this->attr->tab_categories[$_key]);
				}
				unset($_key, $_category, $_term); // Housekeeping.

				$this->attr->tag = preg_split('/,+/', $this->attr->tag, NULL, PREG_SPLIT_NO_EMPTY);
				$this->attr->tag = $this->plugin->utils_array->remove_emptys($this->plugin->utils_string->trim_deep($this->attr->tag));
				foreach($this->attr->tag as $_key => &$_tag) // Validate each tag.
				{
					if(is_numeric($_tag))
					{
						if(!($_tag = (integer)$_tag))
							unset($this->attr->tag[$_key]);
						continue; // All done here.
					}
					$_term = get_term_by('slug', $_tag, $this->plugin->post_type.'_tag');
					if(!$_term || !($_tag = (integer)$_term->term_id))
						unset($this->attr->tag[$_key]);
				}
				unset($_key, $_tag, $_term); // Housekeeping.

				$this->attr->q   = trim((string)$this->attr->q);
				$this->attr->url = trim((string)$this->attr->url);
			}

			/**
			 * Shortcode parser.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Parsed shortcode; i.e. HTML markup.
			 */
			public function parse()
			{
				$attr            = $this->attr;
				$attr_           = $this->attr_;
				$tab_categories  = $this->tab_categories();
				$tags            = $this->tags();
				$query           = $this->query();
				$pagination_vars = (object)array(
					'per_page'     => $this->attr->per_page,
					'current_page' => $this->attr->page,
					'total_pages'  => $query->max_num_pages,
				);
				$template_vars   = get_defined_vars();
				$template        = new template('site/articles/list.php');

				return $template->parse($template_vars);
			}

			/**
			 * Categories for tabs.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return array An array of category terms.
			 *
			 * @throws \exception On failure to retrieve tab categories.
			 */
			protected function tab_categories()
			{
				$args = array(
					'orderby'    => 'none',
					'hide_empty' => FALSE,
					'include'    => $this->attr->tab_categories,
				);
				if(!$args['include']) return array();

				if(is_wp_error($_ = $categories = get_terms($this->plugin->post_type.'_category', $args)))
					throw new \exception(sprintf(__('Failure to retreive tab categories. %1$s', $this->plugin->text_domain), $_->get_error_message()));

				return $categories;
			}

			/**
			 * All of the KB article tags.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return array An array of tag terms.
			 *
			 * @throws \exception On failure to retrieve tags.
			 */
			protected function tags()
			{
				$args = array(
					'orderby'    => 'name',
					'order'      => 'ASC',
					'hide_empty' => FALSE,
				);
				if(is_wp_error($_ = $tags = get_terms($this->plugin->post_type.'_tag', $args)))
					throw new \exception(sprintf(__('Failure to retreive tags. %1$s', $this->plugin->text_domain), $_->get_error_message()));

				return $tags;
			}

			/**
			 * Performs the query.
			 *
			 * @since 150113 First documented version.
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
						'field'            => 'id',
						'include_children' => TRUE,
						'operator'         => 'IN',
					);
				}
				if($this->attr->tag)
				{
					if(empty($args['tax_query']['relation']))
						$args['tax_query']['relation'] = 'AND';

					$args['tax_query'][] = array(
						'taxonomy' => $this->plugin->post_type.'_tag',
						'terms'    => $this->attr->tag,
						'field'    => 'id',
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