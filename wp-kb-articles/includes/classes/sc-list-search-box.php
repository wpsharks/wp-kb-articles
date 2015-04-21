<?php
/**
 * Shortcode for List Search Box
 *
 * @since 150220 Improving search box.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\sc_list_search_box'))
	{
		/**
		 * Shortcode for List Search Box
		 *
		 * @since 150220 Improving search box.
		 */
		class sc_list_search_box extends abs_base
		{
			/**
			 * Raw shortcode attributes.
			 *
			 * @since 150220 Improving search box.
			 *
			 * @var array Raw shortcode attributes.
			 */
			protected $attr_;

			/**
			 * Shortcode attributes.
			 *
			 * @since 150220 Improving search box.
			 *
			 * @var \stdClass Shortcode attributes.
			 */
			protected $attr;

			/**
			 * Shortcode content string.
			 *
			 * @since 150220 Improving search box.
			 *
			 * @var string Shortcode content string.
			 */
			protected $content;

			/**
			 * Class constructor.
			 *
			 * @since 150220 Improving search box.
			 *
			 * @param array  $attr Shortcode attributes.
			 * @param string $content Shortcode content string.
			 */
			public function __construct(array $attr, $content = '')
			{
				parent::__construct();

				$default_attr = array(
					'q'      => '', // Search query.
					'action' => '', // Form action URL/path.
				);
				$attr         = array_merge($default_attr, $attr);
				$attr         = array_intersect_key($attr, $default_attr);

				$this->attr    = (object)$attr;
				$this->attr_   = $attr; // Originals.
				$this->content = (string)$content;

				foreach($this->attr as $_prop => &$_value) // e.g. `q` is all that's supported here for now.
					if(in_array($_prop, $this->plugin->qv_keys, TRUE) && !is_null($_qv = get_query_var($this->plugin->qv_prefix.$_prop, NULL)))
						$_value = urldecode((string)$_qv); // e.g. `q` is all that's supported here for now.
				unset($_prop, $_value, $_qv); // Housekeeping.

				foreach($this->attr as $_prop => &$_value) // e.g. `q` is all that's supported here for now.
					if(in_array($_prop, $this->plugin->qv_keys, TRUE) && isset($_REQUEST[$this->plugin->qv_prefix.$_prop]))
						$_value = trim(stripslashes((string)$_REQUEST[$this->plugin->qv_prefix.$_prop]));
				unset($_prop, $_value); // Housekeeping.

				$this->attr->q      = trim((string)$this->attr->q);
				$this->attr->action = trim((string)$this->attr->action);
			}

			/**
			 * Shortcode parser.
			 *
			 * @since 150220 Improving search box.
			 *
			 * @return string Parsed shortcode; i.e. HTML markup.
			 */
			public function parse()
			{
				$attr          = $this->attr;
				$attr_         = $this->attr_;
				$template_vars = get_defined_vars();
				$template      = new template('site/articles/list-search-box.php');

				return $template->parse($template_vars);
			}
		}
	}
}
