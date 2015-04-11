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
			 * @since 150113 First documented version.
			 *
			 * @var \stdClass Shortcode attributes.
			 */
			protected $attr;

			/**
			 * Query class instance.
			 *
			 * @since 150410 Improving searches.
			 *
			 * @var query Query class instance.
			 */
			protected $query;

			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param array $attr Shortcode attributes.
			 */
			public function __construct(array $attr)
			{
				parent::__construct();

				$default_attr = array_merge(
					query::$default_args, array(
					'tab_categories' => 'trending,popular',
					'url'            => $this->plugin->utils_url->current(),
				));
				if(isset($attr['tab_category']) && !isset($attr['tab_categories']))
					$attr['tab_categories'] = $attr['tab_category'];

				$attr = array_merge($default_attr, $attr);
				$attr = array_intersect_key($attr, $default_attr);

				$this->query = new query($attr); // Perform DB query.
				$this->attr  = array_merge($attr, (array)$this->query->args);
				$this->attr  = (object)$this->attr; // Object now.

				$this->attr->tab_categories = preg_split('/,+/', (string)$this->attr->tab_categories, NULL, PREG_SPLIT_NO_EMPTY);
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

				$this->attr->url                       = trim((string)$this->attr->url);
				$this->attr->strings['tab_categories'] = implode(',', $this->attr->tab_categories);
				$this->attr->strings['url']            = $this->attr->url; // String copy.
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
				$attr  = $this->attr;
				$query = $this->query;

				$tab_categories = $this->tab_categories();
				$tags           = $this->tags();
				$filters        = $this->filters();

				$template_vars = get_defined_vars();
				$template      = new template('site/articles/list.php');

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

				if(is_wp_error($categories = get_terms($this->plugin->post_type.'_category', $args)))
					throw new \exception(sprintf(__('Failure to retreive tab categories. %1$s', $this->plugin->text_domain), $categories->get_error_message()));

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
				if(is_wp_error($tags = get_terms($this->plugin->post_type.'_tag', $args)))
					throw new \exception(sprintf(__('Failure to retreive tags. %1$s', $this->plugin->text_domain), $tags->get_error_message()));

				return $tags;
			}

			/**
			 * Filters that apply.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return array All filters that apply.
			 */
			protected function filters()
			{
				$filters = array(); // Initialize.

				if($this->attr->author) // By author(s)?
				{
					$_authors      = array(); // Initialize.
					$_show_avatars = get_option('show_avatars');

					foreach($this->attr->author as $_author_id)
						if(($_author = get_userdata($_author_id)))
							$_authors[] = ($_show_avatars ? get_avatar($_author->ID, 32).' ' : '').
							              esc_html($_author->display_name ? $_author->display_name : $_author->user_login);

					$filters['author'] = sprintf(__('<strong>%1$s</strong>', $this->plugin->text_domain), implode('</strong>, <strong>', $_authors));

					unset($_authors, $_show_avatars, $_author_id, $_author); // Housekeeping.
				}
				if($this->attr->category) // By category(s)?
				{
					$_categories = array(); // Initialize.

					foreach($this->attr->category as $_term_id)
						if(($_term = get_term_by('id', $_term_id, $this->plugin->post_type.'_category')))
							$_categories[] = esc_html($_term->name ? $_term->name : $_term->slug);

					$filters['category'] = sprintf(__('<strong>%1$s</strong>', $this->plugin->text_domain), implode('</strong>, <strong>', $_categories));

					unset($_categories, $_term_id, $_term); // Housekeeping.
				}
				if($this->attr->tag) // By tag(s)?
				{
					$_tags = array(); // Initialize.

					foreach($this->attr->tag as $_term_id)
						if(($_term = get_term_by('id', $_term_id, $this->plugin->post_type.'_tag')))
							$_tags[] = esc_html($_term->name ? $_term->name : $_term->slug);

					$filters['tag'] = sprintf(__('<strong>%1$s</strong>', $this->plugin->text_domain), implode('</strong>, <strong>', $_tags));

					unset($_tags, $_term_id, $_term); // Housekeeping.
				}
				if($this->attr->q) // By search term(s)?
				{
					$filters['q'] = sprintf(__('<strong>%1$s</strong>', $this->plugin->text_domain), esc_html($this->attr->q));
				}
				return $filters; // An array of all filters.
			}
		}
	}
}
