<?php
namespace wp_kb_articles;

/**
 * @var plugin    $plugin Plugin class.
 * @var template  $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass $attr Parsed/normalized/validated shortcode attributes.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>

<?php
echo $template->snippet(
	'list-search-box.php', array(

	'[namespace]' => esc_attr(__NAMESPACE__),

	'[name]'      => esc_attr($plugin->qv_prefix.'q'),
	'[action]'    => esc_attr($attr->action),
	'[q]'         => esc_attr($attr->q),
));
?>

