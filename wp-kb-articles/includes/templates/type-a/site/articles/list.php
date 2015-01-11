<?php
namespace wp_kb_articles;

/**
 * @var plugin      $plugin Plugin class.
 * @var template    $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass[] $tab_categories An array of categories; for tabs.
 * @var \stdClass[] $tags An array of all KB article tags.
 * @var \stdClass   $attr Parsed/normalized/validated shortcode attributes.
 * @var array       $attr_ Unparsed/raw shortcode attributes.
 * @var \WP_Query   $query WP Query class instance ready for iteration.
 * @var \stdClass   $pagination_vars Object containing pagination vars.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<div class="<?php echo esc_attr(__NAMESPACE__.'-list'); ?>">

	<?php if($tab_categories): ?>
		<div class="<?php echo esc_attr(__NAMESPACE__.'-tabs'); ?>">
			<ul>
				<?php foreach($tab_categories as $_tab_category): ?>
					<li><a href="#" data-category="<?php echo esc_attr($_tab_category->term_id); ?>"><?php echo esc_html($_tab_category->name); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if($tags): ?>
		<div class="<?php echo esc_attr(__NAMESPACE__.'-tags'); ?>">
			<ul>
				<?php foreach($tags as $_tag): ?>
					<li><a href="#" data-tag="<?php echo esc_attr($_tag->term_id); ?>"><?php echo esc_html($_tag->name); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if($query->have_posts()): ?>
		<div class="<?php echo esc_attr(__NAMESPACE__.'-articles'); ?>">
			<?php while($query->have_posts()): $query->the_post(); ?>
				<div class="<?php echo esc_attr(__NAMESPACE__.'-article'); ?>">

					<div class="<?php echo esc_attr(__NAMESPACE__.'-article-title'); ?>">
						<a href="<?php echo get_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
					</div>

					<div class="<?php echo esc_attr(__NAMESPACE__.'-article-popularity'); ?>">
						<?php echo esc_html($plugin->utils_post->get_popularity(get_the_ID())); ?>
					</div>

					<div class="<?php echo esc_attr(__NAMESPACE__.'-article-author'); ?>">
						<span><?php echo __('by:', $plugin->text_domain); ?></span>
						<a href="#" data-author="<?php echo esc_attr(get_the_author_meta('ID')); ?>"><?php echo esc_html(get_the_author()); ?></a>
					</div>

					<div class="<?php echo esc_attr(__NAMESPACE__.'-article-tags'); ?>">
						<?php foreach((array)get_the_terms(get_the_ID(), $plugin->post_type.'_tag') as $_tag): if($_tag): ?>
							<a href="#" data-tag="<?php echo esc_attr($_tag->term_id); ?>"><?php echo esc_attr($_tag->name); ?></a>
						<?php endif; endforeach; // End the iteration of each tag. ?>
					</div>

					<?php if(comments_open() || get_comments_number()): ?>
						<div class="<?php echo esc_attr(__NAMESPACE__.'-article-comments'); ?>">
							<a href="<?php echo esc_attr(get_comments_link()); ?>"><?php echo esc_html(get_comments_number_text()); ?></a>
						</div>
					<?php endif; ?>

					<div class="<?php echo esc_attr(__NAMESPACE__.'-article-date'); ?>">
						<?php echo esc_html(get_the_date()); ?>
					</div>

				</div>
			<?php endwhile; ?>
		</div>
		<?php wp_reset_postdata(); ?>
	<?php endif; ?>

	<?php if($pagination_vars->total_pages > 1): ?>
		<div class="<?php echo esc_attr(__NAMESPACE__.'-pagination'); ?>">
			<div class="<?php echo esc_attr(__NAMESPACE__.'-pagination-current-page'); ?>">
				<?php echo sprintf(__('Page %1$s of %2$s', $plugin->text_domain), esc_html($pagination_vars->current_page), esc_html($pagination_vars->total_pages)); ?>
			</div>
			<div class="<?php echo esc_attr(__NAMESPACE__.'-pagination-pages'); ?>">
				<ul>
					<?php if($pagination_vars->current_page > 1): // Create a previous page link? ?>
						<li><a href="#" data-page="<?php echo esc_attr($pagination_vars->current_page - 1); ?>">&laquo;</a></li>
					<?php else: // Not possible; this is the first page. ?>
						<li class="<?php echo esc_attr(__NAMESPACE__.'-pagination-page-disabled'); ?>"><a href="#">&laquo;</a></li>
					<?php endif; ?>

					<?php // Individual page links now.
					$_max_page_links           = 5; // Max individual page links to show on each page.
					$_page_links_start_at_page = // This is a mildly complex calculation that we can do w/ help from the plugin class.
						$plugin->utils_db->pagination_links_start_page($pagination_vars->current_page, $pagination_vars->total_pages, $_max_page_links);

					for($_i = 1, $_page = $_page_links_start_at_page; $_i <= $_max_page_links && $_page <= $pagination_vars->total_pages; $_i++ && $_page++): ?>
						<li<?php if($_page === $pagination_vars->current_page): ?> class="<?php echo esc_attr(__NAMESPACE__.'-pagination-page-current'); ?>"<?php endif; ?>>
							<a href="#" data-page="<?php echo esc_attr($_page); ?>"><?php echo esc_html($_page); ?></a>
						</li>
					<?php endfor; ?>

					<?php if($pagination_vars->current_page < $pagination_vars->total_pages): // Create a next page link? ?>
						<li><a href="#" data-page="<?php echo esc_attr($pagination_vars->current_page + 1); ?>">&raquo;</a></li>
					<?php else: // Not possible; this is the last page. ?>
						<li class="<?php echo esc_attr(__NAMESPACE__.'-pagination-page-disabled'); ?>"><a href="#">&raquo;</a></li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<div class="<?php echo esc_attr(__NAMESPACE__.'-attr'); ?>" data-attr="<?php echo esc_attr($plugin->utils_enc->encrypt(serialize($attr_))); ?>"></div>

</div>