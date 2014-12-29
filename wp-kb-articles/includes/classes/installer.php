<?php
/**
 * Install Routines
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\installer'))
	{
		/**
		 * Install Routines
		 *
		 * @since 141111 First documented version.
		 */
		class installer extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->plugin->setup();

				$this->maybe_enqueue_notice();
				$this->set_install_time();
			}

			/**
			 * First time install displays notice.
			 *
			 * @since 141111 First documented version.
			 */
			protected function maybe_enqueue_notice()
			{
				if(get_option(__NAMESPACE__.'_install_time'))
					return; // Not applicable.

				$notice_markup = $this->plugin->utils_fs->inline_icon_svg().
				                 ' '.sprintf(__('%1$s&trade; installed successfully! Please <a href="%2$s"><strong>click here to configure</strong></a> basic options.', $this->plugin->text_domain),
				                             esc_html($this->plugin->name), esc_attr($this->plugin->utils_url->main_menu_page_only()));

				$this->plugin->enqueue_user_notice($notice_markup); // A quick reminder to configure options.
			}

			/**
			 * Update installation time.
			 *
			 * @since 141111 First documented version.
			 */
			protected function set_install_time()
			{
				if(!get_option(__NAMESPACE__.'_install_time'))
					update_option(__NAMESPACE__.'_install_time', time());
			}
		}
	}
}