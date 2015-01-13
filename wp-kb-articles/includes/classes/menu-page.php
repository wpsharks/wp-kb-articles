<?php
/**
 * Menu Pages
 *
 * @since 150113 First documented version.
 * @copyright WebSharks, Inc. <http://www.websharks-inc.com>
 * @license GNU General Public License, version 3
 */
namespace wp_kb_articles // Root namespace.
{
	if(!defined('WPINC')) // MUST have WordPress.
		exit('Do NOT access this file directly: '.basename(__FILE__));

	if(!class_exists('\\'.__NAMESPACE__.'\\menu_page'))
	{
		/**
		 * Menu Pages
		 *
		 * @since 150113 First documented version.
		 */
		class menu_page extends abs_base
		{
			/**
			 * Class constructor.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $which Which menu page to display?
			 */
			public function __construct($which)
			{
				parent::__construct();

				$which = $this->plugin->utils_string->trim((string)$which, '', '_');
				if($which && method_exists($this, $which.'_'))
					$this->{$which.'_'}();
			}

			/**
			 * Displays menu page.
			 *
			 * @since 150113 First documented version.
			 */
			protected function options_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-options-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-options-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-options '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Plugin Options', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Basic Configuration (Required)', $this->plugin->text_domain).
				     '            <small><span'.($this->plugin->install_time() > strtotime('-1 hour') ? ' class="pmp-hilite"' : '').'>'.
				     sprintf(__('Review these basic options and %1$s&trade; will be ready-to-go!', $this->plugin->text_domain), esc_html($this->plugin->name)).'</span></small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Uninstall on Plugin Deletion, or Safeguard Data?', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'uninstall_safeguards_enable',
						               'current_value'   => $current_value_for('uninstall_safeguards_enable'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => array(
							               '1' => __('Safeguards on; i.e. protect my plugin options &amp; articles (recommended)', $this->plugin->text_domain),
							               '0' => sprintf(__('Safeguards off; uninstall (completely erase) %1$s on plugin deletion', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('By default, if you delete %1$s using the plugins menu in WordPress, no data is lost. However, if you want to completely uninstall %1$s you should turn Safeguards off, and <strong>THEN</strong> deactivate &amp; delete %1$s from the plugins menu in WordPress. This way %1$s will erase your options for the plugin, erase database tables created by the plugin, remove articles, terminate CRON jobs, etc. In short, when Safeguards are off, %1$s erases itself from existence completely when you delete it.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Data Safeguards', $this->plugin->text_domain), $_panel_body, array());

				$_page_options = array('' => '');
				if(($_pages = get_pages())) foreach($_pages as $_page)
					$_page_options[$_page->ID] = $_page->post_title;

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => __('Articles List Shortcode Page', $this->plugin->text_domain),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'sc_articles_list_index_post_id',
						               'current_value'   => $current_value_for('sc_articles_list_index_post_id'),
						               'allow_arbitrary' => FALSE, // Must be one of these.
						               'options'         => $_page_options,
						               'notes_after'     => '<p>'.sprintf(__('If you\'re using the <code>[kb_articles_list /]</code> shortcode on a specific Page in WordPress, please choose that Page from the list. This will tell %1$s to use that Page for any links within WordPress that reference categories/tags associated with KB Articles; i.e. clicking a link which leads to the list of articles associated with a specific category/tag will lead users to this Page where your list is displayed by the <code>[kb_articles_list /]</code> shortcode.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Articles List Shortcode Page', $this->plugin->text_domain), $_panel_body, array());

				unset($_page_options, $_pages, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <h2 class="pmp-section-heading">'.
				     '            '.__('Advanced Configuration (All Optional)', $this->plugin->text_domain).
				     '            <small>'.__('Recommended for advanced site owners only; already pre-configured for most WP installs.', $this->plugin->text_domain).'</small>'.
				     '         </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_panel_body = '<table style="margin-bottom:0;">'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Enable GitHub API Integration and Pull KB Articles from a Repo?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'field_class'     => 'pmp-if-change', // JS change handler.
						               'name'            => 'github_processing_enable',
						               'current_value'   => $current_value_for('github_processing_enable'),
						               'allow_arbitrary' => FALSE,
						               'options'         => array(
							               '0' => __('No, disable GitHub repo integration', $this->plugin->text_domain),
							               '1' => __('Yes, I want to pull KB articles from a GitHub repo', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('This allows you to pull KB articles (written in Markdown) from a GitHub repo and even integrate %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://github.com/websharks/wp-kb-articles/wiki/YAML-Front-Matter-for-GitHub-Integration', __('YAML Front Matter', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				$_panel_body .= '<div class="pmp-if-enabled-show"><hr />'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Repo Owner:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. johndoe, acme-corp', $this->plugin->text_domain),
						                'name'          => 'github_mirror_owner',
						                'current_value' => $current_value_for('github_mirror_owner'),
						                'notes_after'   => '<p>'.__('i.e. https://github.com/<code>owner</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Repo Name:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. kb, acme-kb', $this->plugin->text_domain),
						                'name'          => 'github_mirror_repo',
						                'current_value' => $current_value_for('github_mirror_repo'),
						                'notes_after'   => '<p>'.__('i.e. https://github.com/owner/<code>repo</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Repo Branch:', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. HEAD, master, 000000-dev', $this->plugin->text_domain),
						                'name'          => 'github_mirror_branch',
						                'current_value' => $current_value_for('github_mirror_branch'),
						                'notes_after'   => '<p>'.__('i.e. https://github.com/owner/repo/tree/<code>branch</code>', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'type'          => 'password',
						                'label'         => __('oAuth Token (or Personal Access Token):', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. x6x3g9tpxuebatqn3ssbb9nabv8ymmc6z3ba7tbg', $this->plugin->text_domain),
						                'name'          => 'github_mirror_api_key',
						                'current_value' => $current_value_for('github_mirror_api_key'),
						                'notes_after'   => '<p>'.sprintf(__('Required for private repos and to remove API connection limits imposed on public access. Please generate your %1$s.', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://github.com/settings/applications', __('personal access token', $this->plugin->text_domain))).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                '<hr />'.

				                ' <table>'.
				                '    <tbody>'.
				                $form_fields->input_row(
					                array(
						                'label'         => __('Default Post Author (WP Username):', $this->plugin->text_domain),
						                'placeholder'   => __('e.g. johndoe', $this->plugin->text_domain),
						                'name'          => 'github_mirror_author',
						                'current_value' => $current_value_for('github_mirror_author'),
						                'notes_after'   => '<p>'.sprintf(__('If your %1$s does not specify an <code>author:</code>, who should be set as a default author?', $this->plugin->text_domain), $this->plugin->utils_markup->x_anchor('https://github.com/websharks/wp-kb-articles/wiki/YAML-Front-Matter-for-GitHub-Integration', __('YAML Front Matter', $this->plugin->text_domain))).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <table style="margin-bottom:0;">'.
				                '    <tbody>'.
				                $form_fields->select_row(
					                array(
						                'label'           => sprintf(__('Parse Markdown (<code>.md</code> files) Into HTML?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						                'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						                'name'            => 'github_markdown_parse',
						                'current_value'   => $current_value_for('github_markdown_parse'),
						                'allow_arbitrary' => FALSE,
						                'options'         => array(
							                '1' => __('Yes, parse Markdown files into HTML and save the post content as HTML markup', $this->plugin->text_domain),
							                '0' => __('No, leave as-is; I am using a Markdown parser in WordPress already', $this->plugin->text_domain),
						                ),
						                'notes_after'     => '<p>'.__('This should almost always be Yes. However, if you are running a theme that parses Markdown in Posts already, you could set this to no.', $this->plugin->text_domain).'</p>',
					                )).
				                '    </tbody>'.
				                ' </table>'.

				                ' <hr />'.

				                ' <p class="pmp-note pmp-notice">'.sprintf(__('With all of these credentials in place, %1$s&trade; will begin to mirror your GitHub repo; pulling all <code>.md</code> and/or <code>.html</code> files from your repo into WordPress. See also: %2$s. The %1$s&trade; GitHub repo processor runs once every 15 minutes. It looks at the SHA1 hash of each article in your repo and compares this to articles in WordPress. If updates are necessary, changes will be pulled automatically and WordPress is updated to match your repo.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor('https://github.com/websharks/wp-kb-articles/wiki/YAML-Front-Matter-for-GitHub-Integration', __('YAML Front Matter', $this->plugin->text_domain))).'</p>'.

				                ' </table>'.

				                '</div>';

				echo $this->panel(__('GitHub Repo Integration', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Simple Templates or Advanced PHP Templates?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'template_type',
						               'current_value'   => $current_value_for('template_type'),
						               'allow_arbitrary' => FALSE,
						               'options'         => array(
							               's' => __('Simple shortcode templates (default; easiest to work with)', $this->plugin->text_domain),
							               'a' => __('Advanced PHP-based templates (for developers and advanced site owners)', $this->plugin->text_domain),
						               ),
						               'notes_after'     => '<p>'.__('<strong>Note:</strong> If you change this setting, any template customizations that you\'ve made in one mode, will need to be done again for the new mode that you select; i.e. when this setting is changed, a new set of templates is loaded for the mode you select. You can always switch back though, and any changes that you made in the previous mode will be restored automatically.', $this->plugin->text_domain).'</p>'.
						                                    '<p class="pmp-note pmp-info">'.sprintf(__('<strong>Tip:</strong> You\'ll notice that by changing this setting, all of the customizable templates in %1$s will be impacted; i.e. when you select %2$s from the menu at the top, a new set of templates will load-up; based on the mode that you choose here. You can also switch modes <em>while</em> you\'re editing templates (see: %2$s). That will impact this setting in the exact same way. Change it here or change it there, no difference.', $this->plugin->text_domain), esc_html($this->plugin->name), $this->plugin->utils_markup->x_anchor($this->plugin->utils_url->site_templates_menu_page_only(), __('Site Templates', $this->plugin->text_domain))).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Template-Related Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_panel_body = '<table>'.
				               '  <tbody>'.
				               $form_fields->select_row(
					               array(
						               'label'           => sprintf(__('Display %1$s&trade; Logo in Admin Area?', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               'placeholder'     => __('Select an Option...', $this->plugin->text_domain),
						               'name'            => 'menu_pages_logo_icon_enable',
						               'current_value'   => $current_value_for('menu_pages_logo_icon_enable'),
						               'allow_arbitrary' => FALSE,
						               'options'         => array(
							               '1' => sprintf(__('Yes, enable %1$s&trade; logo in back-end administrative areas', $this->plugin->text_domain), esc_html($this->plugin->name)),
							               '0' => sprintf(__('No, disable %1$s&trade; logo in back-end administrative areas', $this->plugin->text_domain), esc_html($this->plugin->name)),
						               ),
						               'notes_after'     => '<p>'.sprintf(__('Enabling/disabling the logo in back-end areas does not impact any functionality; it\'s simply a personal preference.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>',
					               )).
				               '  </tbody>'.
				               '</table>';

				echo $this->panel(__('Misc. UI-Related Settings', $this->plugin->text_domain), $_panel_body, array());

				unset($_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 150113 First documented version.
			 */
			protected function import_export_()
			{
				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-import-export '.$this->plugin->slug.'-menu-page-area').'">'."\n";

				echo '   '.$this->heading(__('Import/Export', $this->plugin->text_domain), 'logo.png').
				     '   '.$this->notes(); // Heading/notifications.

				echo '   <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				/* ----------------------------------------------------------------------------------------- */

				echo '      <h2 class="pmp-section-heading">'.
				     '         '.__('Import/Export Config. Options', $this->plugin->text_domain).
				     '         <small>'.sprintf(__('This allows you to import/export %1$s&trade; configuration options.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</small>'.
				     '      </h2>';

				/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-import-ops-form',
					'ns_name_suffix' => '[import]',
					'class_prefix'   => 'pmp-import-ops-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Import a New Set of %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.sprintf(__('Configuration options are imported using a JSON-encoded file obtained from another copy of %1$s&trade;.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.sprintf(__('<strong>Tip:</strong> To save time you can import your options from another WordPress installation where you\'ve already configured %1$s&trade; before.', $this->plugin->text_domain), esc_html($this->plugin->name)).'</p>';

				$_panel_body .= ' <table>'.
				                '   <tbody>'.
				                $_form_fields->input_row(
					                array(
						                'type'        => 'file',
						                'label'       => __('JSON Config. Options File:', $this->plugin->text_domain),
						                'placeholder' => __('e.g. config-options.json', $this->plugin->text_domain),
						                'name'        => 'data_file',
					                )).
				                '   </tbody>'.
				                ' </table>';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Import JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-upload"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('Import Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-upload"></i>'));

				unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				$_form_field_args = array(
					'ns_id_suffix'   => '-export-ops-form',
					'ns_name_suffix' => '[export]',
					'class_prefix'   => 'pmp-export-ops-form-',
				);
				$_form_fields     = new form_fields($_form_field_args);

				$_panel_body = '<form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				$_panel_body .= ' <h3 style="margin-bottom:0;">'.sprintf(__('Export All of your %1$s&trade; Config. Options', $this->plugin->text_domain), esc_html($this->plugin->name)).'</h3>';
				$_panel_body .= ' <p>'.__('Configuration options are downloaded as a JSON-encoded file.', $this->plugin->text_domain).'</p>';
				$_panel_body .= ' <p class="pmp-note pmp-info" style="font-size:90%;">'.__('<strong>Tip:</strong> Export your configuration on this site, and then import it into another WordPress installation to save time in the future.', $this->plugin->text_domain).'</p>';

				$_panel_body .= ' <div style="display:none;">'.
				                '  '.$_form_fields->hidden_input(array('name' => 'type', 'current_value' => 'ops')).
				                ' </div>';

				$_panel_body .= ' <button type="submit" style="width:100%;">'.
				                '  '.__('Download JSON Config. Options File', $this->plugin->text_domain).' <i class="fa fa-download"></i>'.
				                ' </button>';

				$_panel_body .= '</form>';

				echo $this->panel(__('Export Config. Options', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-download"></i>'));

				unset($_form_field_args, $_form_fields, $_panel_body); // Housekeeping.

				/* ----------------------------------------------------------------------------------------- */

				echo '   </div>'."\n";
				echo '</div>';
			}

			/**
			 * Displays menu page.
			 *
			 * @since 150113 First documented version.
			 */
			protected function site_templates_()
			{
				$_this             = $this;
				$form_field_args   = array(
					'ns_id_suffix'   => '-site-templates-form',
					'ns_name_suffix' => '[save_options]',
					'class_prefix'   => 'pmp-site-templates-form-',
				);
				$form_fields       = new form_fields($form_field_args);
				$current_value_for = function ($key) use ($_this)
				{
					if(strpos($key, 'template__') === 0 && isset($_this->plugin->options[$key]))
					{
						if($_this->plugin->options[$key])
							return $_this->plugin->options[$key];

						$data             = template::option_key_data($key);
						$default_template = new template($data->file, $data->type, TRUE);

						return $default_template->file_contents();
					}
					return isset($_this->plugin->options[$key]) ? $_this->plugin->options[$key] : NULL;
				};
				$shortcode_details = function ($shortcodes) use ($_this)
				{
					$detail_lis = array(); // Initialize.

					foreach($shortcodes as $_shortcode => $_details)
						$detail_lis[] = '<li><code>'.esc_html($_shortcode).'</code>&nbsp;&nbsp;'.$_details.'</li>';
					unset($_shortcode, $_details); // Housekeeping.

					if($detail_lis) // If we have shortcodes, let's list them.
						$details = '<ul class="pmp-list-items" style="margin-top:0; margin-bottom:0;">'.implode('', $detail_lis).'</ul>';
					else $details = __('No shortcodes for this template at the present time.', $_this->plugin->text_domain);

					return '<a href="#" data-toggle="alert" data-alert="'.esc_attr($details).'">'.__('shortcodes explained', $_this->plugin->text_domain).'</a>';
				};
				/* ----------------------------------------------------------------------------------------- */

				echo '<div class="'.esc_attr($this->plugin->slug.'-menu-page '.$this->plugin->slug.'-menu-page-site-templates '.$this->plugin->slug.'-menu-page-area').'">'."\n";
				echo '   <form method="post" enctype="multipart/form-data" action="'.esc_attr($this->plugin->utils_url->page_nonce_only()).'" novalidate="novalidate">'."\n";

				echo '      '.$this->heading(__('Site Templates', $this->plugin->text_domain), 'logo.png').
				     '      '.$this->notes(); // Heading/notifications.

				echo '      <div class="pmp-body">'."\n";

				echo '         '.$this->all_panel_togglers();

				if($this->plugin->is_pro) // Only possible in the pro version.
				{
					echo '      <div class="pmp-template-types pmp-right">'.
					     '         <span>'.__('Template Mode:', $this->plugin->text_domain).'</span>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('s')).'"'.($this->plugin->options['template_type'] === 's' ? ' class="pmp-active"' : '').'>'.__('simple', $this->plugin->text_domain).'</a>'.
					     '         <a href="'.esc_attr($this->plugin->utils_url->set_template_type('a')).'"'.($this->plugin->options['template_type'] === 'a' ? ' class="pmp-active"' : '').'>'.__('advanced', $this->plugin->text_domain).'</a>'.
					     '      </div>';
				}
				/* ----------------------------------------------------------------------------------------- */

				if($this->plugin->options['template_type'] === 's') // Simple snippet-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Templates for Articles List Shortcode', $this->plugin->text_domain).
					     '            <small>'.__('These are used by the <code>[kb_articles_list /]</code> shortcode.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Single Article Listing', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__articles__snippet__list_article___php',
							               'current_value' => $current_value_for('template__type_s__site__articles__snippet__list_article___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes a single row in the list; i.e. the details displayed for each article as the shortcode iterates over all of the results in the current list.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     'comments_open'          => __('Are comments open on the current article?', $this->plugin->text_domain),
								                                                     'comments_number'        => __('How many comments the current article has.', $this->plugin->text_domain),
								                                                     'show_avatars'           => __('Has the blog been configured to display avatars?', $this->plugin->text_domain),
								                                                     '[namespace]'            => __('The plugin\'s namespace; used in class/id/name generation.', $this->plugin->text_domain),
								                                                     '[post_id]'              => __('The numeric WP post ID for the current article.', $this->plugin->text_domain),
								                                                     '[permalink]'            => __('The permalink/URL leading to the current article.', $this->plugin->text_domain),
								                                                     '[title]'                => __('Title of the current article.', $this->plugin->text_domain),
								                                                     '[popularity]'           => __('Popularity score for the current article.', $this->plugin->text_domain),
								                                                     '[author_id]'            => __('The numeric WP author ID; for the current article author.', $this->plugin->text_domain),
								                                                     '[author_posts_url]'     => __('URL leading to other posts by the author of the current article.', $this->plugin->text_domain),
								                                                     '[author_avatar]'        => __('An HTML &lt;img&gt; tag with an avatar for the current article\'s author.', $this->plugin->text_domain),
								                                                     '[author]'               => __('Author of the current article; i.e. author\'s display name.', $this->plugin->text_domain),
								                                                     '[tags]'                 => __('A comma-delimited list of clickable tags the current article has.', $this->plugin->text_domain),
								                                                     '[comments_number_text]' => __('How many comments the current article has; e.g. No Comments, 1 Comment, 4 Comments.', $this->plugin->text_domain),
								                                                     '[date]'                 => __('The current article\'s publication date.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Single Article Listing', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Templates for Article Footer', $this->plugin->text_domain).
					     '            <small>'.__('These are used at the bottom of each KB article.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Article Footer', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/html',
							               'name'          => 'template__type_s__site__articles__snippet__footer___php',
							               'current_value' => $current_value_for('template__type_s__site__articles__snippet__footer___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the HTML markup output at the bottom of KB articles.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
							               'cm_details'    => $shortcode_details(array(
								                                                     'comments_open'          => __('Are comments open on the current article?', $this->plugin->text_domain),
								                                                     'comments_number'        => __('How many comments the current article has.', $this->plugin->text_domain),
								                                                     'show_avatars'           => __('Has the blog been configured to display avatars?', $this->plugin->text_domain),
								                                                     '[namespace]'            => __('The plugin\'s namespace; used in class/id/name generation.', $this->plugin->text_domain),
								                                                     '[post_id]'              => __('The numeric WP post ID for the current article.', $this->plugin->text_domain),
								                                                     '[permalink]'            => __('The permalink/URL leading to the current article.', $this->plugin->text_domain),
								                                                     '[title]'                => __('Title of the current article.', $this->plugin->text_domain),
								                                                     '[popularity]'           => __('Popularity score for the current article.', $this->plugin->text_domain),
								                                                     '[author_id]'            => __('The numeric WP author ID; for the current article author.', $this->plugin->text_domain),
								                                                     '[author_posts_url]'     => __('URL leading to other posts by the author of the current article.', $this->plugin->text_domain),
								                                                     '[author_avatar]'        => __('An HTML &lt;img&gt; tag with an avatar for the current article\'s author.', $this->plugin->text_domain),
								                                                     '[author]'               => __('Author of the current article; i.e. author\'s display name.', $this->plugin->text_domain),
								                                                     '[tags]'                 => __('A comma-delimited list of clickable tags the current article has.', $this->plugin->text_domain),
								                                                     '[comments_number_text]' => __('How many comments the current article has; e.g. No Comments, 1 Comment, 4 Comments.', $this->plugin->text_domain),
								                                                     '[date]'                 => __('The current article\'s publication date.', $this->plugin->text_domain),
							                                                     )),
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Article Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				else if($this->plugin->options['template_type'] === 'a') // Advanced PHP-based templates.
				{
					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Templates for Articles List Shortcode', $this->plugin->text_domain).
					     '            <small>'.__('These are used by the <code>[kb_articles_list /]</code> shortcode.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Articles List Shortcode', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__articles__list___php',
							               'current_value' => $current_value_for('template__type_a__site__articles__list___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the HTML markup output by the <code>[kb_articles_list /]</code> shortcode. See comments in the template file for further details.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Articles List Shortcode', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Articles List Styles', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/css',
							               'name'          => 'template__type_a__site__articles__list___css',
							               'current_value' => $current_value_for('template__type_a__site__articles__list___css'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the CSS used by the <code>[kb_articles_list /]</code> shortcode.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Articles List Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Articles List Scripts', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__articles__list___js___php',
							               'current_value' => $current_value_for('template__type_a__site__articles__list___js___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the JavaScript used by the <code>[kb_articles_list /]</code> shortcode.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Articles List Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.

					/* ----------------------------------------------------------------------------------------- */

					echo '         <h2 class="pmp-section-heading">'.
					     '            '.__('Templates for Article Footer', $this->plugin->text_domain).
					     '            <small>'.__('These are used at the bottom of each KB article.', $this->plugin->text_domain).'</small>'.
					     '         </h2>';

					/* --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- */

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Article Footer', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__articles__footer___php',
							               'current_value' => $current_value_for('template__type_a__site__articles__footer___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the HTML markup output at the bottom of KB articles. See comments in the template file for further details.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Article Footer', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Article Footer Styles', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'text/css',
							               'name'          => 'template__type_a__site__articles__footer___css',
							               'current_value' => $current_value_for('template__type_a__site__articles__footer___css'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the CSS used by the the Article Footer template.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Article Footer Styles', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					$_panel_body = '<table>'.
					               '  <tbody>'.
					               $form_fields->textarea_row(
						               array(
							               'label'         => __('Article Footer Scripts', $this->plugin->text_domain),
							               'placeholder'   => __('Template Content...', $this->plugin->text_domain),
							               'cm_mode'       => 'application/x-httpd-php',
							               'name'          => 'template__type_a__site__articles__footer___js___php',
							               'current_value' => $current_value_for('template__type_a__site__articles__footer___js___php'),
							               'notes_before'  => '<p class="pmp-note pmp-notice">'.__('<strong>Note:</strong> The default template is already optimized for most WordPress installs; i.e. you shouldn\'t need to customize. However, if you don\'t like the defaults; tweak things a bit until you reach perfection <i class="fa fa-smile-o"></i>', $this->plugin->text_domain).'</p>'.
							                                  '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> this particular template establishes all of the JavaScript used by the the Article Footer template.', $this->plugin->text_domain).'</p>',
							               'notes_after'   => '<p class="pmp-note pmp-info">'.__('<strong>Tip:</strong> If you mess up your template by accident; empty the field completely and save your options. This reverts you back to the default template file automatically.', $this->plugin->text_domain).'</p>',
						               )).
					               '  </tbody>'.
					               '</table>';

					echo $this->panel(__('Article Footer Scripts', $this->plugin->text_domain), $_panel_body, array('icon' => '<i class="fa fa-code"></i>'));

					unset($_panel_body); // Housekeeping.
				}
				/* ----------------------------------------------------------------------------------------- */

				echo '         <div class="pmp-save">'."\n";
				echo '            <button type="submit">'.__('Save All Changes', $this->plugin->text_domain).' <i class="fa fa-save"></i></button>'."\n";
				echo '         </div>'."\n";

				echo '      </div>'."\n";
				echo '   </form>'."\n";
				echo '</div>';
			}

			/**
			 * Constructs menu page heading.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $title Title of this menu page.
			 * @param string $logo_icon Logo/icon for this menu page.
			 *
			 * @return string The heading for this menu page.
			 */
			protected function heading($title, $logo_icon = '')
			{
				$title     = (string)$title;
				$logo_icon = (string)$logo_icon;
				$heading   = ''; // Initialize.

				$heading .= '<div class="pmp-heading">'."\n";

				if($logo_icon && $this->plugin->options['menu_pages_logo_icon_enable'])
					$heading .= '  <img class="pmp-logo-icon" src="'.$this->plugin->utils_url->to('/client-s/images/'.$logo_icon).'" alt="'.esc_attr($title).'" />'."\n";

				$heading .= '  <div class="pmp-heading-links">'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->main_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__) ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-gears"></i> '.__('Options', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->import_export_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_import_export') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-upload"></i> '.__('Import/Export', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->site_templates_menu_page_only()).'"'.
				            ($this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_site_templates') ? ' class="pmp-active"' : '').'>'.
				            '<i class="fa fa-code"></i> '.__('Site Templates', $this->plugin->text_domain).'</a>'."\n";

				$heading .= '     <a href="#" data-pmp-action="'.esc_attr($this->plugin->utils_url->restore_default_options()).'" data-pmp-confirmation="'.esc_attr(__('Restore default plugin options? You will lose all of your current settings! Are you absolutely sure?', $this->plugin->text_domain)).'"><i class="fa fa-ambulance"></i> '.__('Restore Default Options', $this->plugin->text_domain).'</a>'."\n";

				if(!$this->plugin->is_pro) // Display pro preview/upgrade related links?
				{
					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->pro_preview()).'"'.
					            ($this->plugin->utils_env->is_pro_preview() ? ' class="pmp-active"' : '').'>'.
					            '<i class="fa fa-eye"></i> '.__('Preview Pro Features', $this->plugin->text_domain).'</a>'."\n";

					$heading .= '  <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="fa fa-heart-o"></i> '.__('Pro Upgrade', $this->plugin->text_domain).'</a>'."\n";
				}
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->subscribe_page()).'" target="_blank"><i class="fa fa-envelope-o"></i> '.__('Newsletter (Subscribe)', $this->plugin->text_domain).'</a>'."\n";
				$heading .= '     <a href="'.esc_attr($this->plugin->utils_url->product_page()).'" target="_blank"><i class="wsi wsi-wp-kb-articles"></i> '.esc_html($this->plugin->site_name).'</a>'."\n";

				$heading .= '  </div>'."\n";

				$heading .= '</div>'."\n";

				return $heading; // Menu page heading.
			}

			/**
			 * All-panel togglers.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string Markup for all-panel togglers.
			 */
			protected function all_panel_togglers()
			{
				$togglers = '<div class="pmp-all-panel-togglers">'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-open" title="'.esc_attr(__('Open All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-down"></i></a>'."\n";
				$togglers .= ' <a href="#" class="pmp-panels-close" title="'.esc_attr(__('Close All Panels', $this->plugin->text_domain)).'"><i class="fa fa-chevron-circle-up"></i></a>'."\n";
				$togglers .= '</div>'."\n";

				return $togglers; // Toggles all panels open/closed.
			}

			/**
			 * Constructs menu page notes.
			 *
			 * @since 150113 First documented version.
			 *
			 * @return string The notes for this menu page.
			 */
			protected function notes()
			{
				$notes = ''; // Initialize notes.

				if($this->plugin->utils_env->is_pro_preview())
				{
					$notes .= '<div class="pmp-note pmp-info">'."\n";
					$notes .= '  <a href="'.esc_attr($this->plugin->utils_url->page_only()).'" style="float:right; margin:0 0 15px 25px; font-variant:small-caps; text-decoration:none;">'.__('close', $this->plugin->text_domain).' <i class="fa fa-eye-slash"></i></a>'."\n";
					$notes .= '  <i class="fa fa-eye"></i> '.sprintf(__('<strong>Pro Features (Preview)</strong> ~ New option panels below. Please explore before <a href="%1$s" target="_blank">upgrading <i class="fa fa-heart-o"></i></a>.', $this->plugin->text_domain), esc_attr($this->plugin->utils_url->product_page())).'<br />'."\n";
					$notes .= '  '.sprintf(__('<small>NOTE: the free version of %1$s (i.e. this lite version); is more-than-adequate for most sites. Please upgrade only if you desire advanced features or would like to support the developer.</small>', $this->plugin->text_domain), esc_html($this->plugin->name))."\n";
					$notes .= '</div>'."\n";
				}
				if($this->plugin->install_time() > strtotime('-48 hours') && $this->plugin->utils_env->is_menu_page(__NAMESPACE__.'_*_templates'))
				{
					$notes .= '<div class="pmp-note pmp-notice">'."\n";
					$notes .= '  '.__('All templates come preconfigured; customization is optional <i class="fa fa-smile-o"></i>', $this->plugin->text_domain)."\n";
					$notes .= '</div>'."\n";
				}
				return $notes; // All notices; if any apply.
			}

			/**
			 * Constructs a menu page panel.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $title Panel title.
			 * @param string $body Panel body; i.e. HTML markup.
			 * @param array  $args Any additional specs/behavorial args.
			 *
			 * @return string Markup for this menu page panel.
			 */
			protected function panel($title, $body, array $args = array())
			{
				$title = (string)$title;
				$body  = (string)$body;

				$default_args = array(
					'note'     => '',
					'icon'     =>
						'<i class="fa fa-gears"></i>',
					'pro_only' => FALSE,
					'open'     => FALSE,
				);
				$args         = array_merge($default_args, $args);
				$args         = array_intersect_key($args, $default_args);

				$note     = trim((string)$args['note']);
				$icon     = trim((string)$args['icon']);
				$pro_only = (boolean)$args['pro_only'];
				$open     = (boolean)$args['open'];

				if($pro_only && !$this->plugin->is_pro && !$this->plugin->utils_env->is_pro_preview())
					return ''; // Not applicable; not pro, or not a pro preview.

				$panel = '<div class="pmp-panel'.esc_attr($pro_only && !$this->plugin->is_pro ? ' pmp-pro-preview' : '').'">'."\n";
				$panel .= '   <a href="#" class="pmp-panel-heading'.($open ? ' open' : '').'">'."\n";
				$panel .= '      '.$icon.' '.$title."\n";
				$panel .= $note ? '<span class="pmp-panel-heading-note">'.$note.'</span>' : '';
				$panel .= '   </a>'."\n";

				$panel .= '   <div class="pmp-panel-body'.($open ? ' open' : '').' pmp-clearfix">'."\n";

				$panel .= '      '.$body."\n";

				$panel .= '   </div>'."\n";
				$panel .= '</div>'."\n";

				return $panel; // Markup for this panel.
			}

			/**
			 * Constructs a select-all input field value.
			 *
			 * @since 150113 First documented version.
			 *
			 * @param string $label_markup HTML markup for label.
			 * @param string $value Current value to be selected in the input field.
			 *
			 * @return string Markup for this select-all input field value.
			 */
			protected function select_all_field($label_markup, $value)
			{
				$label_markup = trim((string)$label_markup);
				$value        = trim((string)$value);

				return // Select-all input field value.

					'<table style="table-layout:auto;">'.
					'  <tr>'.
					'     <td style="display:table-cell; white-space:nowrap;">'.
					'        '.$label_markup.
					'     </td>'.
					'     <td style="display:table-cell; width:100%;" title="'.__('select all; copy', $this->plugin->text_domain).'">'.
					'        <input type="text" value="'.esc_attr($value).'" readonly="readonly" data-toggle="select-all" style="cursor:pointer; color:#333333; background:#FFFFFF;" />'.
					'     </td>'.
					'  </tr>'.
					'</table>';
			}
		}
	}
}