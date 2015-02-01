<?php
/**
 * Install Routines
 *
 * @since 150113 First documented version.
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
		 * @since 150113 First documented version.
		 */
		class installer extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->plugin->setup();

				$this->create_db_tables();
				$this->activate_post_type_role_caps();
				$this->maybe_enqueue_notice();
				$this->flush_rewrite_rules();
				$this->set_install_time();
			}

			/**
			 * Create DB tables.
			 *
			 * @since 150131 Adding statistics.
			 *
			 * @throws \exception If table creation fails.
			 */
			protected function create_db_tables()
			{
				foreach(scandir($tables_dir = dirname(dirname(__FILE__)).'/tables') as $_sql_file)
					if(substr($_sql_file, -4) === '.sql' && is_file($tables_dir.'/'.$_sql_file))
					{
						$_sql_file_table = substr($_sql_file, 0, -4);
						$_sql_file_table = str_replace('-', '_', $_sql_file_table);
						$_sql_file_table = $this->plugin->utils_db->prefix().$_sql_file_table;

						$_sql = file_get_contents($tables_dir.'/'.$_sql_file);
						$_sql = str_replace('%%prefix%%', $this->plugin->utils_db->prefix(), $_sql);
						$_sql = $this->plugin->utils_db->fulltext_compat($_sql);

						if(!$this->plugin->utils_db->wp->query($_sql)) // Table creation failure?
							throw new \exception(sprintf(__('DB table creation failure. Table: `%1$s`. SQL: `%2$s`.', $this->plugin->text_domain), $_sql_file_table, $_sql));
					}
				unset($_sql_file, $_sql_file_table, $_sql); // Housekeeping.
			}

			/**
			 * Activate post type role caps.
			 *
			 * @since 150113 First documented version.
			 */
			public function activate_post_type_role_caps()
			{
				$this->plugin->post_type_role_caps('activate');
			}

			/**
			 * First time install displays notice.
			 *
			 * @since 150113 First documented version.
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
			 * Flush rewrite rules.
			 *
			 * @since 150113 First documented version.
			 */
			public function flush_rewrite_rules()
			{
				flush_rewrite_rules();
			}

			/**
			 * Update installation time.
			 *
			 * @since 150113 First documented version.
			 */
			protected function set_install_time()
			{
				if(!get_option(__NAMESPACE__.'_install_time'))
					update_option(__NAMESPACE__.'_install_time', time());
			}
		}
	}
}