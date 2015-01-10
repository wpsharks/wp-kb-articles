<?php
namespace wp_kb_articles;

/**
 * @var plugin    $plugin Plugin class.
 * @var template  $template Template class.
 *
 * Other variables made available in this template file:
 *
 * @var \stdClass $attr Parsed/normalized/validated shortcode attributes.
 * @var \WP_Query $query WP Query class instance ready for iteration.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<?php if($query->have_posts()): ?>
	<?php while($query->have_posts): $query->the_post(); ?>

		<div class="<?php echo esc_attr(__NAMESPACE__.'-item'); ?>">

			<div class="<?php echo esc_attr(__NAMESPACE__.'-title'); ?>">
				<?php echo esc_html(get_the_title()); ?>
			</div>

			<div class="<?php echo esc_attr(__NAMESPACE__.'-popularity'); ?>">
				<?php echo $plugin->utils_post->get_popularity(get_the_ID()); ?>
			</div>

			<div class="<?php echo esc_attr(__NAMESPACE__.'-author'); ?>">
				<a href="<?php echo esc_attr(get_author_posts_url(get_the_author_meta('ID'))); ?>"><span><?php echo __('by:', $plugin->text_domain); ?></span> <?php echo esc_html(get_the_author()); ?></a>
			</div>

			<div class="<?php echo esc_attr(__NAMESPACE__.'-tags'); ?>">
				<?php echo implode(', ', (array)get_the_terms(get_the_ID(), $plugin->post_type.'_tag')); ?>
			</div>

			<?php if(comments_open() || get_comments_number()): ?>
				<div class="<?php echo esc_attr(__NAMESPACE__.'-comments'); ?>">
					<a href="<?php echo esc_attr(get_comments_link()); ?>"><?php echo esc_html(get_comments_number_text()); ?></a>
				</div>
			<?php endif; ?>

			<div class="<?php echo esc_attr(__NAMESPACE__.'-date'); ?>">
				<?php echo esc_html(get_the_date()); ?>
			</div>

		</div>

	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
<?php endif; ?>