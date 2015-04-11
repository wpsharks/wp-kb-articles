<?php
namespace wp_kb_articles;

/**
 * @var plugin   $plugin Plugin class.
 * @var template $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \WP_Post $post WordPress post object reference.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php $_tags = ''; // Initialize.
if(($_terms = get_the_terms($post->ID, $plugin->post_type.'_tag')) && !is_wp_error($_terms)):
	foreach($_terms as $_term) // Iterate the tags that it has.
		$_tags .= ($_tags ? ', ' : ''). // Comma-delimited tags.
		          '<a href="'.esc_attr(get_term_link($_term)).'">'.esc_attr($_term->name).'</a>';
endif; // End if article has tags.
unset($_terms, $_term); // Housekeeping.

echo $template->snippet(
	'footer.php', array(

	'tags'                   => $_tags,
	'comments_open'          => comments_open(),
	'comments_number'        => get_comments_number(),
	'show_avatars'           => get_option('show_avatars'),
	'current_user_can_edit'  => current_user_can('edit_post', $post->ID),

	'[namespace]'            => esc_attr(__NAMESPACE__),

	'[post_id]'              => esc_html($post->ID),
	'[permalink]'            => esc_attr(get_permalink()),
	'[title]'                => esc_html(get_the_title()),

	'[popularity]'           => esc_html($plugin->utils_post->get_popularity($post->ID)),

	'[author_id]'            => esc_attr(get_the_author_meta('ID')),
	'[author_posts_url]'     => esc_attr(get_author_posts_url(get_the_author_meta('ID'))),
	'[author_avatar]'        => get_avatar(get_the_author_meta('ID'), 64),
	'[author]'               => esc_html(get_the_author()),

	'[tags]'                 => $_tags, // Contains raw HTML markup.

	'[comments_number_text]' => esc_html(get_comments_number_text()),
	'[date]'                 => esc_html(get_the_date()),
));
unset($_tags); // Housekeeping.
?>
