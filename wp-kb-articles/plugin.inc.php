<?php
/**
 * Plugin Class
 *
 * @since 141111 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	require_once dirname(__FILE__).'/includes/classes/abs-base.php';

	if(!defined('WP_KB_ARTICLE_ROLES_ALL_CAPS'))
		/**
		 * @var string Back compat. constant with original release.
		 */
		define('WP_KB_ARTICLE_ROLES_ALL_CAPS', 'administrator');

	if(!defined('WP_KB_ARTICLE_ROLES_EDIT_CAPS'))
		/**
		 * @var string Back compat. constant with original release.
		 */
		define('WP_KB_ARTICLE_ROLES_EDIT_CAPS', 'administrator,editor,author');

	if(!class_exists('\\'.__NAMESPACE__.'\\plugin'))
	{
		/**
		 * Plugin Class
		 *
		 * @property-read utils_array           $utils_array
		 * @property-read utils_date            $utils_date
		 * @property-read utils_db              $utils_db
		 * @property-read utils_enc             $utils_enc
		 * @property-read utils_env             $utils_env
		 * @property-read utils_fs              $utils_fs
		 * @property-read utils_github          $utils_github
		 * @property-read utils_i18n            $utils_i18n
		 * @property-read utils_ip              $utils_ip
		 * @property-read utils_log             $utils_log
		 * @property-read utils_markup          $utils_markup
		 * @property-read utils_math            $utils_math
		 * @property-read utils_php             $utils_php
		 * @property-read utils_string          $utils_string
		 * @property-read utils_url             $utils_url
		 * @property-read utils_user            $utils_user
		 *
		 * @since 141111 First documented version.
		 */
		class plugin extends abs_base
		{
			/*
			 * Public Properties
			 */

			/**
			 * Identifies pro version.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var boolean `TRUE` for pro version.
			 */
			public $is_pro = TRUE;

			/**
			 * Plugin name.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin name.
			 */
			public $name = 'WP KB Articles';

			/**
			 * Plugin name (abbreviated).
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin name (abbreviated).
			 */
			public $short_name = 'WPKBA';

			/**
			 * Site name.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Site name.
			 */
			public $site_name = 'websharks-inc.com';

			/**
			 * Plugin product page URL.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Plugin product page URL.
			 */
			public $product_url = 'http://www.websharks-inc.com/product/wp-kb-articles/';

			/**
			 * Post type w/ underscores.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Post type w/ underscores.
			 */
			public $post_type = 'kb_article';

			/**
			 * Post type w/ dashes.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Post type w/ dashes.
			 */
			public $post_type_slug = 'kb-article';

			/**
			 * Used by the plugin's uninstall handler.
			 *
			 * @since 141111 Adding uninstall handler.
			 *
			 * @var boolean Defined by constructor.
			 */
			public $enable_hooks;

			/**
			 * Text domain for translations; based on `__NAMESPACE__`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by class constructor; for translations.
			 */
			public $text_domain;

			/**
			 * Plugin slug; based on `__NAMESPACE__`.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by constructor.
			 */
			public $slug;

			/**
			 * Stub `__FILE__` location.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Defined by class constructor.
			 */
			public $file;

			/**
			 * Version string in YYMMDD[+build] format.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Current version of the software.
			 */
			public $version = '141206';

			/*
			 * Public Properties (Defined @ Setup)
			 */

			/**
			 * An array of all default option values.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Default options array.
			 */
			public $default_options;

			/**
			 * Configured option values.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Options configured by site owner.
			 */
			public $options;

			/**
			 * General capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to administer.
			 *    i.e. to use any aspect of the plugin, including the configuration
			 *    of any/all plugin options and/or advanced settings.
			 */
			public $cap; // Most important cap.

			/**
			 * Auto-recompile capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to auto-recompile.
			 *    i.e. to see notices regarding automatic recompilations
			 *    following an upgrade the plugin files/version.
			 */
			public $auto_recompile_cap;

			/**
			 * Upgrade capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to upgrade.
			 *    i.e. the ability to run any sort of plugin upgrader.
			 */
			public $upgrade_cap;

			/**
			 * Uninstall capability requirement.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var string Capability required to uninstall.
			 *    i.e. the ability to deactivate and even delete the plugin.
			 */
			public $uninstall_cap;

			/**
			 * Roles to receive all KB article caps.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Roles to receive all KB article caps.
			 */
			public $roles_recieving_all_caps = array();

			/**
			 * Roles to receive KB article edit caps.
			 *
			 * @since 141111 First documented version.
			 *
			 * @var array Roles to receive KB article edit caps.
			 */
			public $roles_recieving_edit_caps = array();

			/*
			 * Public Properties (Defined by Various Hooks)
			 */

			public $menu_page_hooks = array();

			/*
			 * Plugin Constructor
			 */

			/**
			 * Plugin constructor.
			 *
			 * @param boolean $enable_hooks Defaults to a TRUE value.
			 *    If FALSE, setup runs but without adding any hooks.
			 *
			 * @since 141111 First documented version.
			 */
			public function __construct($enable_hooks = TRUE)
			{
				/*
				 * Parent constructor.
				 */
				$GLOBALS[__NAMESPACE__] = $this; // Global ref.
				parent::__construct(); // Run parent constructor.

				/*
				 * Initialize properties.
				 */
				$this->enable_hooks = (boolean)$enable_hooks;
				$this->text_domain  = $this->slug = str_replace('_', '-', __NAMESPACE__);
				$this->file         = preg_replace('/\.inc\.php$/', '.php', __FILE__);

				/*
				 * Initialize autoloader.
				 */
				require_once dirname(__FILE__).'/includes/classes/autoloader.php';
				new autoloader(); // Register the plugin's autoloader.

				/*
				 * With or without hooks?
				 */
				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; construct without hooks.

				/*
				 * Setup primary plugin hooks.
				 */
				add_action('after_setup_theme', array($this, 'setup'));
				register_activation_hook($this->file, array($this, 'activate'));
				register_deactivation_hook($this->file, array($this, 'deactivate'));
			}

			/*
			 * Setup Routine(s)
			 */

			/**
			 * Setup the plugin.
			 *
			 * @since 141111 First documented version.
			 */
			public function setup()
			{
				/*
				 * Setup already?
				 */
				if(!is_null($setup = &$this->cache_key(__FUNCTION__)))
					return; // Already setup. Once only!
				$setup = TRUE; // Once only please.

				/*
				 * Fire pre-setup hooks.
				 */
				if($this->enable_hooks) // Hooks enabled?
					do_action('before__'.__METHOD__, get_defined_vars());

				/*
				 * Load the plugin's text domain for translations.
				 */
				load_plugin_textdomain($this->text_domain); // Translations.

				/*
				 * Setup class properties related to authentication/capabilities.
				 */
				$this->cap                = apply_filters(__METHOD__.'_cap', 'activate_plugins');
				$this->auto_recompile_cap = apply_filters(__METHOD__.'_auto_recompile_cap', 'activate_plugins');
				$this->upgrade_cap        = apply_filters(__METHOD__.'_upgrade_cap', 'update_plugins');
				$this->uninstall_cap      = apply_filters(__METHOD__.'_uninstall_cap', 'delete_plugins');

				/*
				 * Setup the array of all plugin options.
				 */
				$this->default_options = array(
					/* Core/systematic option keys. */

					'version'                             => $this->version,
					'crons_setup'                         => '0', // `0` or timestamp.

					/* Related to data safeguards. */

					'uninstall_safeguards_enable'         => '1', // `0|1`; safeguards on?

					/* Related to GitHub integration. */

					'github_processing_enable'            => '0', // `0|1`; enable?

					'github_mirror_owner'                 => '', // Repo owner.
					'github_mirror_repo'                  => '', // Repo owner.
					'github_mirror_branch'                => '', // Branch.
					'github_mirror_username'              => '', // Username.
					'github_mirror_password'              => '', // Password.
					'github_mirror_api_key'               => '', // API key.
					'github_mirror_author'                => '', // User login|ID.

					'github_markdown_parse'               => '1', // Parse Markdown?

					'github_processor_max_time'           => '30', // In seconds.
					'github_processor_delay'              => '250', // In milliseconds.
					'github_processor_max_limit'          => '100', // Total files.
					'github_processor_realtime_max_limit' => '5', // Total files.

					/* Related to IP tracking. */

					'prioritize_remote_addr'              => '0', // `0|1`; enable?
					'geo_location_tracking_enable'        => '0', // `0|1`; enable?

					/* Related to menu pages; i.e. logo display. */

					'menu_pages_logo_icon_enable'         => '0', // `0|1`; display?

					/* Template-related config. options. */

					'template_type'                       => 's', // `a|s`.

				); // Default options are merged with those defined by the site owner.
				$this->default_options = apply_filters(__METHOD__.'__default_options', $this->default_options); // Allow filters.
				$this->options         = is_array($this->options = get_option(__NAMESPACE__.'_options')) ? $this->options : array();

				$this->options = array_merge($this->default_options, $this->options); // Merge into default options.
				$this->options = array_intersect_key($this->options, $this->default_options); // Valid keys only.
				$this->options = apply_filters(__METHOD__.'__options', $this->options); // Allow filters.
				$this->options = array_map('strval', $this->options); // Force string values.

				if(WP_KB_ARTICLE_ROLES_ALL_CAPS) // Specific Roles?
					$this->roles_recieving_all_caps = // Convert these to an array.
						preg_split('/[\s;,]+/', WP_KB_ARTICLE_ROLES_ALL_CAPS, NULL, PREG_SPLIT_NO_EMPTY);

				if(WP_KB_ARTICLE_ROLES_EDIT_CAPS) // Specific Roles?
					$this->roles_recieving_edit_caps = // Convert these to an array.
						preg_split('/[\s;,]+/', WP_KB_ARTICLE_ROLES_EDIT_CAPS, NULL, PREG_SPLIT_NO_EMPTY);

				/*
				 * With or without hooks?
				 */
				if(!$this->enable_hooks) // Without hooks?
					return; // Stop here; setup without hooks.

				/*
				 * Setup all secondary plugin hooks.
				 */
				add_action('init', array($this, 'actions'), -10, 0);

				add_action('admin_init', array($this, 'check_version'), 10, 0);
				add_action('all_admin_notices', array($this, 'all_admin_notices'), 10, 0);

				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'), 10, 0);
				add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 10, 0);

				add_action('admin_menu', array($this, 'add_menu_pages'), 10, 0);
				add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);
				add_filter('plugin_action_links_'.plugin_basename($this->file), array($this, 'add_settings_link'), 10, 1);

				add_action('wp_print_scripts', array($this, 'enqueue_front_scripts'), 10, 0);

				add_action('init', array($this, 'register_post_type'), 10, 0);

				/*
				 * Setup CRON-related hooks.
				 */
				add_filter('cron_schedules', array($this, 'extend_cron_schedules'), 10, 1);

				if((integer)$this->options['crons_setup'] < 1382523750)
				{
					wp_clear_scheduled_hook('_cron_'.__NAMESPACE__.'_github_processor');
					wp_schedule_event(time() + 60, 'every15m', '_cron_'.__NAMESPACE__.'_github_processor');

					$this->options['crons_setup'] = (string)time();
					update_option(__NAMESPACE__.'_options', $this->options);
				}
				add_action('_cron_'.__NAMESPACE__.'_github_processor', array($this, 'github_processor'), 10);

				/*
				 * Fire setup completion hooks.
				 */
				do_action('after__'.__METHOD__, get_defined_vars());
				do_action(__METHOD__.'_complete', get_defined_vars());
			}

			/*
			 * Magic Methods
			 */

			/**
			 * Magic/overload property getter.
			 *
			 * @param string $property Property to get.
			 *
			 * @return mixed The value of `$this->___overload->{$property}`.
			 *
			 * @throws \exception If the `$___overload` property is undefined.
			 *
			 * @see http://php.net/manual/en/language.oop5.overloading.php
			 */
			public function __get($property)
			{
				$property          = (string)$property;
				$ns_class_property = '\\'.__NAMESPACE__.'\\'.$property;

				if(stripos($property, 'utils_') === 0 && class_exists($ns_class_property))
					if(!isset($this->___overload->{$property})) // Not defined yet?
						$this->___overload->{$property} = new $ns_class_property;

				return parent::__get($property);
			}

			/*
			 * Install-Related Methods
			 */

			/**
			 * First installation time.
			 *
			 * @since 141111 First documented version.
			 *
			 * @return integer UNIX timestamp.
			 */
			public function install_time()
			{
				return (integer)get_option(__NAMESPACE__.'_install_time');
			}

			/**
			 * Plugin activation hook.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to {@link \register_activation_hook()}
			 */
			public function activate()
			{
				new installer(); // Installation handler.
			}

			/**
			 * Check current plugin version.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_init` action.
			 */
			public function check_version()
			{
				if(version_compare($this->options['version'], $this->version, '>='))
					return; // Nothing to do; already @ latest version.

				new upgrader(); // Upgrade handler.
			}

			/*
			 * Uninstall-Related Methods
			 */

			/**
			 * Plugin deactivation hook.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to {@link \register_deactivation_hook()}
			 */
			public function deactivate()
			{
				// Does nothing at this time.
			}

			/**
			 * Plugin uninstall handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @called-by {@link uninstall}
			 */
			public function uninstall()
			{
				new uninstaller(); // Uninstall handler.
			}

			/*
			 * Action-Related Methods
			 */

			/**
			 * Plugin action handler.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `init` action.
			 */
			public function actions()
			{
				if(empty($_REQUEST[__NAMESPACE__]))
					return; // Nothing to do here.

				new actions(); // Handle action(s).
			}

			/*
			 * Option-Related Methods
			 */

			/**
			 * Saves new plugin options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param array $options An array of new plugin options.
			 */
			public function options_save(array $options)
			{
				$this->options = array_merge($this->default_options, $this->options, $options);
				$this->options = array_intersect_key($this->options, $this->default_options);
				$this->options = array_map('strval', $this->options); // Force strings.

				foreach($this->options as $_key => &$_value) if(strpos($_key, 'template__') === 0)
				{
					$_key_data             = template::option_key_data($_key);
					$_default_template     = new template($_key_data->file, $_key_data->type, TRUE);
					$_default_template_nws = preg_replace('/\s+/', '', $_default_template->file_contents());
					$_option_template_nws  = preg_replace('/\s+/', '', $_value);

					if($_option_template_nws === $_default_template_nws)
						$_value = ''; // Empty; it's a default value.
				}
				unset($_key, $_key_data, $_value, // Housekeeping.
					$_default_template, $_option_template_nws, $_default_template_nws);

				update_option(__NAMESPACE__.'_options', $this->options); // DB update.
			}

			/*
			 * Admin Menu-Page-Related Methods
			 */

			/**
			 * Adds CSS for administrative menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action.
			 */
			public function enqueue_admin_styles()
			{
				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*'))
					return; // Nothing to do; not applicable.

				$deps = array('codemirror', 'font-awesome', 'sharkicons'); // Dependencies.

				wp_enqueue_style('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.css'), array(), NULL, 'all');
				wp_enqueue_style('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.css'), array('codemirror'), NULL, 'all');
				wp_enqueue_style('codemirror-ambiance-theme', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/theme/ambiance.min.css'), array('codemirror'), NULL, 'all');

				wp_enqueue_style('font-awesome', set_url_scheme('//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'), array(), NULL, 'all');
				wp_enqueue_style('sharkicons', $this->utils_url->to('/submodules/sharkicons/styles.min.css'), array(), NULL, 'all');

				wp_enqueue_style(__NAMESPACE__, $this->utils_url->to('/client-s/css/menu-pages.min.css'), $deps, $this->version, 'all');
			}

			/**
			 * Adds JS for administrative menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_enqueue_scripts` action.
			 */
			public function enqueue_admin_scripts()
			{
				if(!$this->utils_env->is_menu_page(__NAMESPACE__.'*'))
					return; // Nothing to do; NOT a plugin menu page.

				$deps = array('jquery', 'codemirror'); // Dependencies.

				wp_enqueue_script('codemirror', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/codemirror.min.js'), array(), NULL, TRUE);
				wp_enqueue_script('codemirror-fullscreen', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/display/fullscreen.min.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-matchbrackets', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/addon/edit/matchbrackets.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-htmlmixed', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/htmlmixed/htmlmixed.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-xml', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/xml/xml.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-javascript', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/javascript/javascript.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-css', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/css/css.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-clike', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/clike/clike.js'), array('codemirror'), NULL, TRUE);
				wp_enqueue_script('codemirror-php', set_url_scheme('//cdnjs.cloudflare.com/ajax/libs/codemirror/4.7.0/mode/php/php.js'), array('codemirror'), NULL, TRUE);

				wp_enqueue_script(__NAMESPACE__, $this->utils_url->to('/client-s/js/menu-pages.min.js'), $deps, $this->version, TRUE);

				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_vars', array(
					'pluginUrl'    => rtrim($this->utils_url->to('/'), '/'),
					'ajaxEndpoint' => rtrim($this->utils_url->page_nonce_only(), '/'),
				));
				wp_localize_script(__NAMESPACE__, __NAMESPACE__.'_i18n', array());
			}

			/**
			 * Creates admin menu pages.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `admin_menu` action.
			 */
			public function add_menu_pages()
			{
				if(!current_user_can($this->cap))
					return; // Do not add.

				// Menu page icon uses an SVG graphic.
				$icon = $this->utils_fs->inline_icon_svg();

				$divider = // Dividing line used by various menu items below.
					'<span style="display:block; padding:0; margin:0 0 12px 0; height:1px; line-height:1px; background:#CCCCCC; opacity:0.1;"></span>';

				$child_branch_indent = // Each child branch uses the following UTF-8 char `꜖`; <http://unicode-table.com/en/A716/>.
					'<span style="display:inline-block; margin-left:.5em; position:relative; top:-.2em; left:-.2em; font-weight:normal; opacity:0.2;">&#42774;</span> ';

				$current_menu_page = $this->utils_env->current_menu_page(); // Current menu page slug.

				/* ----------------------------------------- */

				$_menu_title                          = __('Config. Options', $this->text_domain);
				$_page_title                          = $this->name.'&trade; &#10609; '.__('Config. Options', $this->text_domain);
				$this->menu_page_hooks[__NAMESPACE__] = add_submenu_page('edit.php?post_type='.$this->post_type, $_page_title, $_menu_title, $this->cap, __NAMESPACE__, array($this, 'menu_page_options'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__], array($this, 'menu_page_options_screen'));

				$_menu_title                                           = // Visible on-demand only.
					'<small><em>'.$child_branch_indent.__('Import/Export', $this->text_domain).'</em></small>';
				$_page_title                                           = $this->name.'&trade; &#10609; '.__('Import/Export', $this->text_domain);
				$_menu_parent                                          = $current_menu_page === __NAMESPACE__.'_import_export' ? 'edit.php?post_type='.$this->post_type : NULL;
				$this->menu_page_hooks[__NAMESPACE__.'_import_export'] = add_submenu_page($_menu_parent, $_page_title, $_menu_title, $this->cap, __NAMESPACE__.'_import_export', array($this, 'menu_page_import_export'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_import_export'], array($this, 'menu_page_import_export_screen'));

				$_menu_title                                            = // Visible on-demand only.
					'<small><em>'.$child_branch_indent.__('Site Templates', $this->text_domain).'</em></small>';
				$_page_title                                            = $this->name.'&trade; &#10609; '.__('Site Templates', $this->text_domain);
				$_menu_parent                                           = $current_menu_page === __NAMESPACE__.'_site_templates' ? 'edit.php?post_type='.$this->post_type : NULL;
				$this->menu_page_hooks[__NAMESPACE__.'_site_templates'] = add_submenu_page($_menu_parent, $_page_title, $_menu_title, $this->cap, __NAMESPACE__.'_site_templates', array($this, 'menu_page_site_templates'));
				add_action('load-'.$this->menu_page_hooks[__NAMESPACE__.'_site_templates'], array($this, 'menu_page_site_templates_screen'));

				unset($_menu_title, $_page_title, $_menu_parent); // Housekeeping.
			}

			/**
			 * Set plugin-related screen options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `set-screen-option` filter.
			 *
			 * @param mixed|boolean $what_wp_says `FALSE` if not saving (default).
			 *    If we set this to any value besides `FALSE`, the option will be saved by WP.
			 *
			 * @param string        $option The option being checked; i.e. should we save this option?
			 *
			 * @param mixed         $value The current value for this option.
			 *
			 * @return mixed|boolean Returns `$value` for plugin-related options.
			 *    Other we simply return `$what_wp_says`.
			 */
			public function set_screen_option($what_wp_says, $option, $value)
			{
				if(strpos($option, __NAMESPACE__.'_') === 0)
					return $value; // Yes, save this.

				return $what_wp_says;
			}

			/**
			 * Menu page screen; for options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__]` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_options_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__]
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for options.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_options()
			{
				new menu_page('options');
			}

			/**
			 * Menu page screen; for import/export.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_import_export']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_import_export_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_import_export'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_import_export']
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for import/export.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_import_export()
			{
				new menu_page('import_export');
			}

			/**
			 * Menu page screen; for site templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `'load-'.$this->menu_page_hooks[__NAMESPACE__.'_site_templates']` action.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_site_templates_screen()
			{
				$screen = get_current_screen();
				if(!($screen instanceof \WP_Screen))
					return; // Not possible.

				if(empty($this->menu_page_hooks[__NAMESPACE__.'_site_templates'])
				   || $screen->id !== $this->menu_page_hooks[__NAMESPACE__.'_site_templates']
				) return; // Not applicable.

				return; // No screen for this page right now.
			}

			/**
			 * Menu page for site templates.
			 *
			 * @since 141111 First documented version.
			 *
			 * @see add_menu_pages()
			 */
			public function menu_page_site_templates()
			{
				new menu_page('site_templates');
			}

			/**
			 * Adds link(s) to plugin row on the WP plugins page.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `plugin_action_links_'.plugin_basename($this->file)` filter.
			 *
			 * @param array $links An array of the existing links provided by WordPress.
			 *
			 * @return array Revised array of links.
			 */
			public function add_settings_link(array $links)
			{
				$links[] = '<a href="'.esc_attr($this->utils_url->main_menu_page_only()).'">'.__('Settings', $this->text_domain).'</a><br/>';
				if(!$this->is_pro) $links[] = '<a href="'.esc_attr($this->utils_url->pro_preview()).'">'.__('Preview Pro Features', $this->text_domain).'</a>';
				if(!$this->is_pro) $links[] = '<a href="'.esc_attr($this->utils_url->product_page()).'" target="_blank">'.__('Upgrade', $this->text_domain).'</a>';

				return apply_filters(__METHOD__, $links, get_defined_vars());
			}

			/*
			 * Admin Notice/Error Related Methods
			 */

			/**
			 * Enqueue an administrative notice.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $markup HTML markup containing the notice itself.
			 * @param array  $args An array of additional args; i.e. presentation/style.
			 */
			public function enqueue_notice($markup, array $args = array())
			{
				if(!($markup = trim((string)$markup)))
					return; // Nothing to do here.

				$default_args   = array(
					'markup'       => '',
					'requires_cap' => '',
					'for_user_id'  => 0,
					'for_page'     => '',
					'persistent'   => FALSE,
					'transient'    => FALSE,
					'push_to_top'  => FALSE,
					'type'         => 'notice',
				);
				$args['markup'] = (string)$markup; // + markup.
				$args           = array_merge($default_args, $args);
				$args           = array_intersect_key($args, $default_args);

				$args['requires_cap'] = trim((string)$args['requires_cap']);
				$args['requires_cap'] = $args['requires_cap'] // Force valid format.
					? strtolower(preg_replace('/\W/', '_', $args['requires_cap'])) : '';

				$args['for_user_id'] = (integer)$args['for_user_id'];
				$args['for_page']    = trim((string)$args['for_page']);

				$args['persistent']  = (boolean)$args['persistent'];
				$args['transient']   = (boolean)$args['transient'];
				$args['push_to_top'] = (boolean)$args['push_to_top'];

				if(!in_array($args['type'], array('notice', 'error'), TRUE))
					$args['type'] = 'notice'; // Use default type.

				ksort($args); // Sort args (by key) for key generation.
				$key = $this->utils_enc->hmac_sha256_sign(serialize($args));

				if(!is_array($notices = get_option(__NAMESPACE__.'_notices')))
					$notices = array(); // Force an array of notices.

				if($args['push_to_top']) // Push this notice to the top?
					$this->utils_array->unshift_assoc($notices, $key, $args);
				else $notices[$key] = $args; // Default behavior.

				update_option(__NAMESPACE__.'_notices', $notices);
			}

			/**
			 * Enqueue an administrative notice; for a particular user.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_notice()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_user_notice($markup, array $args = array())
			{
				if(!isset($args['for_user_id']))
					$args['for_user_id'] = get_current_user_id();

				$this->enqueue_notice($markup, $args);
			}

			/**
			 * Enqueue an administrative error.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_notice()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_error($markup, array $args = array())
			{
				$this->enqueue_notice($markup, array_merge($args, array('type' => 'error')));
			}

			/**
			 * Enqueue an administrative error; for a particular user.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $markup HTML markup. See {@link enqueue_error()}.
			 * @param array  $args Additional args. See {@link enqueue_notice()}.
			 */
			public function enqueue_user_error($markup, array $args = array())
			{
				if(!isset($args['for_user_id']))
					$args['for_user_id'] = get_current_user_id();

				$this->enqueue_error($markup, $args);
			}

			/**
			 * Render admin notices; across all admin dashboard views.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `all_admin_notices` action.
			 */
			public function all_admin_notices()
			{
				if(!is_array($notices = get_option(__NAMESPACE__.'_notices')))
					update_option(__NAMESPACE__.'_notices', ($notices = array()));

				if(!$notices) return; // Nothing more to do in this case.

				$user_can_view_notices = current_user_can($this->cap);

				$original_notices = $notices; // Copy.

				foreach($notices as $_key => $_args)
				{
					$default_args = array(
						'markup'       => '',
						'requires_cap' => '',
						'for_user_id'  => 0,
						'for_page'     => '',
						'persistent'   => FALSE,
						'transient'    => FALSE,
						'push_to_top'  => FALSE,
						'type'         => 'notice',
					);
					$_args        = array_merge($default_args, $_args);
					$_args        = array_intersect_key($_args, $default_args);

					$_args['markup'] = trim((string)$_args['markup']);

					$_args['requires_cap'] = trim((string)$_args['requires_cap']);
					$_args['requires_cap'] = $_args['requires_cap'] // Force valid format.
						? strtolower(preg_replace('/\W/', '_', $_args['requires_cap'])) : '';

					$_args['for_user_id'] = (integer)$_args['for_user_id'];
					$_args['for_page']    = trim((string)$_args['for_page']);

					$_args['persistent']  = (boolean)$_args['persistent'];
					$_args['transient']   = (boolean)$_args['transient'];
					$_args['push_to_top'] = (boolean)$_args['push_to_top'];

					if(!in_array($_args['type'], array('notice', 'error'), TRUE))
						$_args['type'] = 'notice'; // Use default type.

					if($_args['transient']) // Transient; i.e. single pass only?
						unset($notices[$_key]); // Remove always in this case.

					if(!$user_can_view_notices) // Primary capability check.
						continue;  // Don't display to this user under any circumstance.

					if($_args['requires_cap'] && !current_user_can($_args['requires_cap']))
						continue; // Don't display to this user; lacks required cap.

					if($_args['for_user_id'] && get_current_user_id() !== $_args['for_user_id'])
						continue; // Don't display to this particular user ID.

					if($_args['for_page'] && !$this->utils_env->is_menu_page($_args['for_page']))
						continue; // Don't display on this page; i.e. pattern match failure.

					if($_args['markup']) // Only display non-empty notices.
					{
						if($_args['persistent']) // Need [dismiss] link?
						{
							$_dismiss_style = 'float: right;'.
							                  'margin: 0 0 0 15px;'.
							                  'display: inline-block;'.
							                  'text-decoration: none;'.
							                  'font-weight: bold;';
							$_dismiss_url   = $this->utils_url->dismiss_notice($_key);
							$_dismiss       = '<a href="'.esc_attr($_dismiss_url).'"'.
							                  '  style="'.esc_attr($_dismiss_style).'">'.
							                  '  '.__('dismiss &times;', $this->text_domain).
							                  '</a>';
						}
						else $_dismiss = ''; // Default value; n/a.

						$_classes = $this->slug.'-menu-page-area'; // Always.
						$_classes .= ' '.($_args['type'] === 'error' ? 'error' : 'updated');

						$_full_markup = // Put together the full markup; including other pieces.
							'<div class="'.esc_attr($_classes).'">'.
							'  '.$this->utils_string->p_wrap($_args['markup'], $_dismiss).
							'</div>';
						echo apply_filters(__METHOD__.'_notice', $_full_markup, get_defined_vars());
					}
					if(!$_args['persistent']) unset($notices[$_key]); // Once only; i.e. don't show again.
				}
				unset($_key, $_args, $_dismiss_style, $_dismiss_url, $_dismiss, $_classes, $_full_markup); // Housekeeping.

				if($original_notices !== $notices) update_option(__NAMESPACE__.'_notices', $notices);
			}

			/*
			 * Front-Side Scripts
			 */

			public function enqueue_front_scripts()
			{
				new front_scripts();
			}

			/*
			 * CRON-Related Methods
			 */

			/**
			 * Extends WP-Cron schedules.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `cron_schedules` filter.
			 *
			 * @param array $schedules An array of the current schedules.
			 *
			 * @return array Revised array of WP-Cron schedules.
			 */
			public function extend_cron_schedules(array $schedules)
			{
				$schedules['every5m']  = array('interval' => 300, 'display' => __('Every 5 Minutes', $this->text_domain));
				$schedules['every15m'] = array('interval' => 900, 'display' => __('Every 15 Minutes', $this->text_domain));

				return apply_filters(__METHOD__, $schedules, get_defined_vars());
			}

			/**
			 * GitHub processor.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `_cron_'.__NAMESPACE__.'_github_processor` action.
			 */
			public function github_processor()
			{
				new github_processor();
			}

			/*
			 * Custom post type handlers.
			 */

			/**
			 * Regisers post type.
			 *
			 * @since 141111 First documented version.
			 *
			 * @attaches-to `init` action.
			 */
			public function register_post_type()
			{
				// Menu page icon uses an SVG graphic.
				$icon = $this->utils_fs->inline_icon_svg();

				$post_type_args           = array
				(
					'public'       => TRUE,
					'has_archive'  => $this->post_type_slug.'s',
					'menu_icon'    => 'data:image/svg+xml;base64,'.base64_encode($icon),
					'map_meta_cap' => TRUE, 'capability_type' => array($this->post_type, $this->post_type.'s'),
					'rewrite'      => array('slug' => $this->post_type_slug, 'with_front' => FALSE), // Like a Post (but no Post Formats).
					'supports'     => array('title', 'editor', 'author', 'excerpt', 'revisions', 'thumbnail', 'custom-fields', 'comments', 'trackbacks')
				);
				$post_type_args['labels'] = array
				(
					'name'               => __('KB Articles', $this->text_domain),
					'singular_name'      => __('KB Article', $this->text_domain),
					'add_new'            => __('Add KB Article', $this->text_domain),
					'add_new_item'       => __('Add New KB Article', $this->text_domain),
					'edit_item'          => __('Edit KB Article', $this->text_domain),
					'new_item'           => __('New KB Article', $this->text_domain),
					'all_items'          => __('All KB Articles', $this->text_domain),
					'view_item'          => __('View KB Article', $this->text_domain),
					'search_items'       => __('Search KB Articles', $this->text_domain),
					'not_found'          => __('No KB Articles found', $this->text_domain),
					'not_found_in_trash' => __('No KB Articles found in Trash', $this->text_domain)
				);
				register_post_type($this->post_type, $post_type_args);

				$category_taxonomy_args = array // Categories.
				(
				                                'public'       => TRUE, 'show_admin_column' => TRUE,
				                                'hierarchical' => TRUE, // This will use category labels.
				                                'rewrite'      => array('slug' => $this->post_type_slug.'-category', 'with_front' => FALSE),
				                                'capabilities' => array('assign_terms' => 'edit_'.$this->post_type.'s',
				                                                        'edit_terms'   => 'edit_'.$this->post_type.'s',
				                                                        'manage_terms' => 'edit_others_'.$this->post_type.'s',
				                                                        'delete_terms' => 'delete_others_'.$this->post_type.'s')
				);
				register_taxonomy($this->post_type.'_category', array($this->post_type), $category_taxonomy_args);

				$tag_taxonomy_args = array // Tags.
				(
				                           'public'       => TRUE, 'show_admin_column' => TRUE,
				                           'rewrite'      => array('slug' => $this->post_type_slug.'-tag', 'with_front' => FALSE),
				                           'capabilities' => array('assign_terms' => 'edit_'.$this->post_type.'s',
				                                                   'edit_terms'   => 'edit_'.$this->post_type.'s',
				                                                   'manage_terms' => 'edit_others_'.$this->post_type.'s',
				                                                   'delete_terms' => 'delete_others_'.$this->post_type.'s')
				);
				register_taxonomy($this->post_type.'_tag', array($this->post_type), $tag_taxonomy_args);
			}

			/**
			 * Activate or deactivate role-base caps.
			 *
			 * @since 141111 First documented version.
			 *
			 * @param string $action One of `activate` or `deactivate`.
			 */
			public function post_type_role_caps($action)
			{
				$all_caps = array(
					'edit_'.$this->post_type.'s',
					'edit_others_'.$this->post_type.'s',
					'edit_published_'.$this->post_type.'s',
					'edit_private_'.$this->post_type.'s',

					'publish_'.$this->post_type.'s',

					'delete_'.$this->post_type.'s',
					'delete_private_'.$this->post_type.'s',
					'delete_published_'.$this->post_type.'s',
					'delete_others_'.$this->post_type.'s',

					'read_private_'.$this->post_type.'s',
				);
				if($action === 'deactivate') // All on deactivate.
					$_roles = array_keys($GLOBALS['wp_roles']->roles);
				else $_roles = $this->roles_recieving_all_caps;

				foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
					foreach($all_caps as $_cap) switch($action)
					{
						case 'activate': // Activating?

							$_role->add_cap($_cap);

							break; // Break switch handler.

						case 'deactivate': // Deactivating?

							$_role->remove_cap($_cap);

							break; // Break switch handler.
					}
				unset($_roles, $_role, $_cap); // Housekeeping.

				$edit_caps = array(
					'edit_'.$this->post_type.'s',
					'edit_published_'.$this->post_type.'s',

					'publish_'.$this->post_type.'s',

					'delete_'.$this->post_type.'s',
					'delete_published_'.$this->post_type.'s',
				);
				if($action === 'deactivate') // All on deactivate.
					$_roles = array_keys($GLOBALS['wp_roles']->roles);
				else $_roles = $this->roles_recieving_edit_caps;

				foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
					foreach(($action === 'deactivate' ? $all_caps : $edit_caps) as $_cap) switch($action)
					{
						case 'activate': // Activating?

							$_role->add_cap($_cap);

							break; // Break switch handler.

						case 'deactivate': // Deactivating?

							$_role->remove_cap($_cap);

							break; // Break switch handler.
					}
				unset($_roles, $_role, $_cap); // Housekeeping.
			}
		}

		/*
		 * Namespaced Functions
		 */

		/**
		 * Used internally by other classes as an easy way to reference
		 *    the core {@link plugin} class instance.
		 *
		 * @since 141111 First documented version.
		 *
		 * @return plugin Class instance.
		 */
		function plugin() // Easy reference.
		{
			return $GLOBALS[__NAMESPACE__];
		}

		/*
		 * Automatic Plugin Loader
		 */

		/**
		 * A global reference to the plugin.
		 *
		 * @since 141111 First documented version.
		 *
		 * @var plugin Main plugin class.
		 */
		if(!isset($GLOBALS[__NAMESPACE__.'_autoload_plugin']) || $GLOBALS[__NAMESPACE__.'_autoload_plugin'])
			$GLOBALS[__NAMESPACE__] = new plugin(); // Load plugin automatically.
	}

	/*
	 * Catch a scenario where the plugin class already exists.
	 *    Assume both lite/pro are running in this case.
	 */

	else if(empty($GLOBALS[__NAMESPACE__.'_uninstalling'])) add_action('all_admin_notices', function ()
	{
		echo '<div class="error">'. // Notify the site owner.
		     '   <p>'.
		     '      '.sprintf(__('Please disable the lite version of <code>%1$s</code> before activating the pro version.',
		                         str_replace('_', '-', __NAMESPACE__)), esc_html(str_replace('_', '-', __NAMESPACE__))).
		     '   </p>'.
		     '</div>';
	});
}