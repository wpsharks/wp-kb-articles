<?php
/**
 * Actions
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly.');

	if(!class_exists('\\'.__NAMESPACE__.'\\actions'))
	{
		/**
		 * Actions
		 *
		 * @since 150113 First documented version.
		 *
		 * @note (front|back)-end actions share the SAME namespace.
		 *    i.e. `$_REQUEST[__NAMESPACE__][action]`, where `action` should be unique
		 *    across any/all (front|back)-end action handlers.
		 *
		 *    This limitation applies only within each classification (context).
		 *    Front-end actions CAN have the same `[action]` name as a back-end action,
		 *    since they're already called from completely different contexts on-site.
		 */
		class actions extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 */
			public function __construct()
			{
				parent::__construct();

				$this->maybe_do_front_side_actions();
				$this->maybe_do_menu_page_actions();
			}

			/**
			 * Front-side actions.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_do_front_side_actions()
			{
				if(is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do.

				new front_side_actions();
			}

			/**
			 * Menu page actions.
			 *
			 * @since 150113 First documented version.
			 */
			protected function maybe_do_menu_page_actions()
			{
				if(!is_admin())
					return; // Not applicable.

				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do.

				new menu_page_actions();
			}
		}
	}
}