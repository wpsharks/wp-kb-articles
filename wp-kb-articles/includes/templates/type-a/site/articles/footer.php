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
<div class="<?php echo esc_attr(__NAMESPACE__.'-footer'); ?> font-body">

	<div class="-meta">

		<div class="-popularity-tags">
			<div class="-popularity">
				<?php echo esc_html($plugin->utils_post->get_popularity(get_the_ID())); ?>
			</div>
			<?php if(($_terms = get_the_terms(get_the_ID(), $plugin->post_type.'_tag'))): ?>
				<div class="-tags">
					<em><?php echo __('Tagged:', $plugin->text_domain); ?></em>
					<?php $_tags = ''; // Initialize.
					foreach($_terms as $_term) // Iterate the tags that it has.
						$_tags .= ($_tags ? ', ' : ''). // Comma-delimited tags.
						          '<a href="'.esc_attr(get_term_link($_term)).'">'.esc_attr($_term->name).'</a>';
					echo $_tags; // Display the tags now; with possible commas.
					unset($_tags, $_term); // Housekeeping. ?>
				</div>
			<?php endif; // End if article has tags.
			unset($_terms); // Housekeeping.
			?>
		</div>

		<div class="-author-popularity">
			<div class="-author">
				<?php if(get_option('show_avatars')): ?>
					<div class="-avatar">
						<a href="<?php echo esc_attr(get_author_posts_url(get_the_author_meta('ID'))); ?>"
							><?php echo get_avatar(get_the_author_meta('ID'), 64); ?></a>
					</div>
				<?php endif; ?>
				<div class="-byline">
					<span class="-by"><?php echo __('Article written by:', $plugin->text_domain); ?></span>
					<a class="-author" href="<?php echo esc_attr(get_author_posts_url(get_the_author_meta('ID'))); ?>"
						><?php echo esc_html(get_the_author()); ?></a>
					<span class="-date"><?php echo esc_html(get_the_date()); ?></span>
				</div>
			</div>
			<a href="#" class="-popularity" data-post-id="<?php echo esc_attr(get_the_ID()); ?>">
				<span class="-vote">
					<strong><?php echo __('Did you find this article helpful?', $plugin->text_domain); ?></strong>
					<i class="fa fa-hand-o-right"></i> <?php echo __('Let the author know by clicking here!', $plugin->text_domain); ?>
				</span>
				<span class="-thank-you">
					<strong><?php echo __('Thank you!', $plugin->text_domain); ?></strong> <i class="fa fa-smile-o"></i>
					<?php echo __('~ A heart has been given to the author.', $plugin->text_domain); ?>
				</span>
			</a>
		</div>

	</div>

</div>