<?php
/*
Version: 131121
Text Domain: wp-kb-articles
Plugin Name: WP KB Articles

Author URI: http://www.websharks-inc.com/
Author: WebSharks, Inc. (Jason Caldwell)

Plugin URI: http://www.websharks-inc.com/product/wp-kb-articles/
Description: Create KB Articles! This plugin adds a new Post Type.
*/
if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));

if(!defined('WP_KB_ARTICLE_ROLES_ALL_CAPS')) define('WP_KB_ARTICLE_ROLES_ALL_CAPS', 'administrator');
if(!defined('WP_KB_ARTICLE_ROLES_EDIT_CAPS')) define('WP_KB_ARTICLE_ROLES_EDIT_CAPS', 'administrator,editor,author');

class wp_kb_articles // WP KB Articles; a new custom post type for WordPress.
{
	public static $roles_all_caps = array(); // WP Roles; as array.
	public static $roles_edit_caps = array(); // WP Roles; as array.

	public static function init() // Initialize WP KB Articles.
		{
			load_plugin_textdomain('wp-kb-articles');

			if(WP_KB_ARTICLE_ROLES_ALL_CAPS) // Specific Roles?
				wp_kb_articles::$roles_all_caps = // Convert these to an array.
					preg_split('/[\s;,]+/', WP_KB_ARTICLE_ROLES_ALL_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
			wp_kb_articles::$roles_all_caps = apply_filters('wp_kb_article_roles_all_caps', wp_kb_articles::$roles_all_caps);

			if(WP_KB_ARTICLE_ROLES_EDIT_CAPS) // Specific Roles?
				wp_kb_articles::$roles_edit_caps = // Convert these to an array.
					preg_split('/[\s;,]+/', WP_KB_ARTICLE_ROLES_EDIT_CAPS, NULL, PREG_SPLIT_NO_EMPTY);
			wp_kb_articles::$roles_edit_caps = apply_filters('wp_kb_article_roles_edit_caps', wp_kb_articles::$roles_edit_caps);

			wp_kb_articles::register();
		}

	public static function register()
		{
			$post_type_args           = array
			(
				'public'       => TRUE,
				'map_meta_cap' => TRUE, 'capability_type' => array('kb_article', 'kb_articles'),
				'rewrite'      => array('slug' => 'kb-article', 'with_front' => FALSE), // Like a Post (but no Post Formats).
				'supports'     => array('title', 'editor', 'author', 'excerpt', 'revisions', 'thumbnail', 'custom-fields', 'comments', 'trackbacks')
			);
			$post_type_args['labels'] = array
			(
				'name'               => __('KB Articles', 'wp-kb-articles'),
				'singular_name'      => __('KB Article', 'wp-kb-articles'),
				'add_new'            => __('Add KB Article', 'wp-kb-articles'),
				'add_new_item'       => __('Add New KB Article', 'wp-kb-articles'),
				'edit_item'          => __('Edit KB Article', 'wp-kb-articles'),
				'new_item'           => __('New KB Article', 'wp-kb-articles'),
				'all_items'          => __('All KB Articles', 'wp-kb-articles'),
				'view_item'          => __('View KB Article', 'wp-kb-articles'),
				'search_items'       => __('Search KB Articles', 'wp-kb-articles'),
				'not_found'          => __('No KB Articles found', 'wp-kb-articles'),
				'not_found_in_trash' => __('No KB Articles found in Trash', 'wp-kb-articles')
			);
			register_post_type('kb_article', $post_type_args);

			$category_taxonomy_args = array // Categories.
			(
				'public'       => TRUE, 'show_admin_column' => TRUE,
				'hierarchical' => TRUE, // This will use category labels.
				'rewrite'      => array('slug' => 'kb-article-category', 'with_front' => FALSE),
				'capabilities' => array('assign_terms' => 'edit_kb_articles',
				                        'edit_terms'   => 'edit_kb_articles',
				                        'manage_terms' => 'edit_others_kb_articles',
				                        'delete_terms' => 'delete_others_kb_articles')
			);
			register_taxonomy('kb_article_category', array('kb_article'), $category_taxonomy_args);

			$tag_taxonomy_args = array // Tags.
			(
				'public'       => TRUE, 'show_admin_column' => TRUE,
				'rewrite'      => array('slug' => 'kb-article-tag', 'with_front' => FALSE),
				'capabilities' => array('assign_terms' => 'edit_kb_articles',
				                        'edit_terms'   => 'edit_kb_articles',
				                        'manage_terms' => 'edit_others_kb_articles',
				                        'delete_terms' => 'delete_others_kb_articles')
			);
			register_taxonomy('kb_article_tag', array('kb_article'), $tag_taxonomy_args);
		}

	public static function caps($action)
		{
			$all_caps = array // The ability to manage (all caps).
			(
				'edit_kb_articles',
				'edit_others_kb_articles',
				'edit_published_kb_articles',
				'edit_private_kb_articles',

				'publish_kb_articles',

				'delete_kb_articles',
				'delete_private_kb_articles',
				'delete_published_kb_articles',
				'delete_others_kb_articles',

				'read_private_kb_articles'
			);
			if($action === 'deactivate') // All on deactivate.
				$_roles = array_keys($GLOBALS['wp_roles']->roles);
			else $_roles = wp_kb_articles::$roles_all_caps;

			foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
				foreach($all_caps as $_cap) switch($action)
				{
					case 'activate':
							$_role->add_cap($_cap);
							break;

					case 'deactivate':
							$_role->remove_cap($_cap);
							break;
				}
			unset($_roles, $_role, $_cap); // Housekeeping.

			$edit_caps = array // The ability to edit/publish/delete.
			(
				'edit_kb_articles',
				'edit_published_kb_articles',

				'publish_kb_articles',

				'delete_kb_articles',
				'delete_published_kb_articles'
			);
			if($action === 'deactivate') // All on deactivate.
				$_roles = array_keys($GLOBALS['wp_roles']->roles);
			else $_roles = wp_kb_articles::$roles_edit_caps;

			foreach($_roles as $_role) if(is_object($_role = get_role($_role)))
				foreach((($action === 'deactivate') ? $all_caps : $edit_caps) as $_cap) switch($action)
				{
					case 'activate':
							$_role->add_cap($_cap);
							break;

					case 'deactivate':
							$_role->remove_cap($_cap);
							break;
				}
			unset($_roles, $_role, $_cap); // Housekeeping.
		}

	public static function activate()
		{
			wp_kb_articles::init();
			wp_kb_articles::caps('activate');
			flush_rewrite_rules();
		}

	public static function deactivate()
		{
			wp_kb_articles::caps('deactivate');
			flush_rewrite_rules();
		}
}

add_action('init', 'wp_kb_articles::init', 1);
register_activation_hook(__FILE__, 'wp_kb_articles::activate');
register_deactivation_hook(__FILE__, 'wp_kb_articles::deactivate');