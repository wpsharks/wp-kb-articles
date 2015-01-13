<?php
/**
 * Markup Utilities
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\utils_markup'))
	{
		/**
		 * Markup Utilities
		 *
		 * @since 150113 First documented version.
		 */
		class utils_markup extends abs_base
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
			 * Constructs markup for an anchor tag.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $url URL to link to.
			 * @param string $clickable Clickable text/markup.
			 * @param array  $args Any additional specs/behavioral args.
			 *
			 * @return string Markup for an anchor tag.
			 */
			public function anchor($url, $clickable, array $args = array())
			{
				$default_args = array(
					'target'   => '',
					'tabindex' => '-1',
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$target   = (string)$args['target'];
				$tabindex = (integer)$args['tabindex'];

				return '<a href="'.esc_attr($url).'" target="'.esc_attr($target).'" tabindex="'.esc_attr($tabindex).'">'.$clickable.'</a>';
			}

			/**
			 * Constructs markup for an external anchor tag.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $url URL to link to.
			 * @param string $clickable Clickable text/markup.
			 * @param array  $args Any additional specs/behavioral args.
			 *
			 * @return string Markup for an external anchor tag.
			 */
			public function x_anchor($url, $clickable, array $args = array())
			{
				$args = array_merge($args, array('target' => '_blank'));

				return $this->anchor($url, $clickable, $args);
			}

			/**
			 * Constructs markup for a plugin menu page path.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Markup for a plugin menu page path.
			 */
			public function pmp_path()
			{
				$path = '<code class="pmp-path">';
				$path .= __('WP Dashboard', $this->plugin->text_domain);
				# $path .= ' &#10609; '.__('Comments', $this->plugin->text_domain);
				$path .= ' &#10609; '.esc_html($this->plugin->name).'&trade;';

				foreach(func_get_args() as $_path_name)
					$path .= ' &#10609; '.(string)$_path_name;

				$path .= '</code>';

				return $path;
			}

			/**
			 * Markup for select menu options.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param array       $given_ops Options array.
			 *    Keys are option values; values are labels.
			 *
			 * @param string|null $current_value The current value.
			 *
			 * @param array       $args Any additional style-related arguments.
			 *
			 * @return string Markup for select menu options.
			 */
			public function select_options(array $given_ops, $current_value = NULL, array $args = array())
			{
				$_selected_value = NULL; // Initialize.
				$current_value   = isset($current_value)
					? (string)$current_value : NULL;

				$default_args = array(
					'allow_arbitrary' => TRUE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$allow_arbitrary = (boolean)$args['allow_arbitrary'];

				$options = ''; // Initialize.
				// There is no `$allow_empty` argument in this handler.
				// Note that we do NOT setup a default/empty option value here.
				// If you want to `$allow_empty`, provide an empty option of your own please.

				foreach($given_ops as $_option_value => $_option_label)
				{
					$_selected     = ''; // Initialize.
					$_option_value = (string)$_option_value;
					$_option_label = (string)$_option_label;

					if(stripos($_option_value, '@optgroup_open') === 0)
						$options .= '<optgroup label="'.esc_attr($_option_label).'">';

					else if(stripos($_option_value, '@optgroup_close') === 0)
						$options .= '</optgroup>'; // Close.

					else // Normal behavior; another option value/label.
					{
						if(!isset($_selected_value) && isset($current_value))
							if(($_selected = selected($_option_value, $current_value, FALSE)))
								$_selected_value = $_option_value;

						$options .= '<option value="'.esc_attr($_option_value).'"'.$_selected.'>'.
						            '  '.esc_html($_option_label).
						            '</option>';
					}
				}
				unset($_option_value, $_option_label, $_selected); // Housekeeping.

				if($allow_arbitrary) // Allow arbitrary select option?
					if(!isset($_selected_value) && isset($current_value) && $current_value)
						$options .= '<option value="'.esc_attr($current_value).'" selected="selected">'.
						            '  '.esc_html($current_value).
						            '</option>';

				unset($_selected_value); // Housekeeping.

				return $options; // HTML markup.
			}
		}
	}
}