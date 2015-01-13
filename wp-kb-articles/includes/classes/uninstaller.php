<?php
/**
 * Uninstall Routines
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\uninstaller'))
	{
		/**
		 * Uninstall Routines
		 *
		 * @since 150113 First documented version.
		 */
		class uninstaller extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				if($this->plugin->enable_hooks)
					return; // Not a good idea.

				$this->plugin->setup(); // Setup.

				if(!defined('WP_UNINSTALL_PLUGIN'))
					return; // Disallow.

				if(empty($GLOBALS[__NAMESPACE__.'_uninstalling']))
					return; // Expecting uninstall file.

				if($this->plugin->options['uninstall_safeguards_enable'])
					return; // Nothing to do here; safeguarding.

				if(!current_user_can($this->plugin->uninstall_cap))
					return; // Extra layer of security.

				if(!current_user_can($this->plugin->cap))
					return; // Extra layer of security.

				$this->delete_options();
				$this->delete_notices();
				$this->delete_install_time();
				$this->delete_option_keys();
				$this->delete_transient_keys();
				$this->delete_post_meta_keys();
				$this->delete_user_meta_keys();
				$this->deactivate_post_type_role_caps();
				$this->flush_rewrite_rules();
				$this->clear_cron_hooks();
			}

			/**
			 * Delete plugin-related options.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_options()
			{
				delete_option(__NAMESPACE__.'_options');
			}

			/**
			 * Delete plugin-related notices.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_notices()
			{
				delete_option(__NAMESPACE__.'_notices');
			}

			/**
			 * Delete install time.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_install_time()
			{
				delete_option(__NAMESPACE__.'_install_time');
			}

			/**
			 * Deactivate post type role caps.
			 *
			 * @since 150113 First documented version.
			 */
			public function deactivate_post_type_role_caps()
			{
				$this->plugin->post_type_role_caps('deactivate');
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
			 * Clear scheduled CRON hooks.
			 *
			 * @since 150113 First documented version.
			 */
			protected function clear_cron_hooks()
			{
				wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_github_processor');
			}

			/**
			 * Delete option keys.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_option_keys()
			{
				$like = // e.g. Delete all keys LIKE `%wp\_kb\_articles%`.
					'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

				$sql = // Removes any other option keys for this plugin.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->options)."`".
					" WHERE `option_name` LIKE '".esc_sql($like)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete transient keys.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_transient_keys()
			{
				$like1 = // e.g. Delete all keys LIKE `%\_transient\_wpkbart\_%`.
					'%'.$this->plugin->utils_db->wp->esc_like('_transient_'.$this->plugin->transient_prefix).'%';

				$like2 = // e.g. Delete all keys LIKE `%\_transient\_timeout\_wpkbart\_%`.
					'%'.$this->plugin->utils_db->wp->esc_like('_transient_timeout_'.$this->plugin->transient_prefix).'%';

				// Note: the above LIKE queries need to match `_site_transient_*` also; and they do.

				$sql = // This will remove our transients/timeouts.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->options)."`".
					" WHERE `option_name` LIKE '".esc_sql($like1)."' OR `option_name` LIKE '".esc_sql($like2)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete post meta keys.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_post_meta_keys()
			{
				$like = // e.g. Delete all keys LIKE `%wp\_kb\_articles%`.
					'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

				$sql = // This will remove our StCR import history also.
					"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->postmeta)."`".
					" WHERE `meta_key` LIKE '".esc_sql($like)."'";

				$this->plugin->utils_db->wp->query($sql);
			}

			/**
			 * Delete user meta keys.
			 *
			 * @since 150113 First documented version.
			 */
			protected function delete_user_meta_keys()
			{
				if(is_multisite()) // Prefixed keys on networks.
				{
					$ms_prefix = $this->plugin->utils_db->wp->prefix;

					$like = $this->plugin->utils_db->wp->esc_like($ms_prefix).
					        // e.g. Delete all keys LIKE `wp\_5\_%wp\_kb\_articles%`.
					        // Or, on the main site it might be: `wp\_%wp\_kb\_articles%`.
					        '%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

					$sql = // This will delete all screen options too.
						"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->usermeta)."`".
						" WHERE `meta_key` LIKE '".esc_sql($like)."'";
				}
				else // No special considerations; there is only one blog.
				{
					$like = // e.g. Delete all keys LIKE `%wp\_kb\_articles%`.
						'%'.$this->plugin->utils_db->wp->esc_like(__NAMESPACE__).'%';

					$sql = // This will delete all screen options too.
						"DELETE FROM `".esc_sql($this->plugin->utils_db->wp->usermeta)."`".
						" WHERE `meta_key` LIKE '".esc_sql($like)."'";
				}
				$this->plugin->utils_db->wp->query($sql);
			}
		}
	}
}