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

<div class="<?php echo esc_attr(__NAMESPACE__.'-list-search-box'); ?>">
	<form method="get" action="<?php echo esc_attr($attr->action); ?>" novalidate>
		<input name="<?php echo esc_attr($plugin->qv_prefix.'q'); ?>"
		       type="search" class="-q" value="<?php echo esc_attr($attr->q); ?>"
		       placeholder="<?php echo esc_attr(__('Search KB Articles...', $plugin->text_domain)); ?>" />
		<button type="button" class="-button">
			<i class="fa fa-search"></i>
		</button>
	</form>
</div>