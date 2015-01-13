<?php
namespace wp_kb_articles;
/**
 * @var plugin   $plugin Plugin class.
 * @var template $template Template class.
 *
 * -------------------------------------------------------------------
 * @note In addition to plugin-specific variables & functionality,
 *    you may also use any WordPress functions that you like.
 */
?>
<script type="text/javascript">
	(function($) // WP KB Articles.
	{
		'use strict'; // Strict standards.

		var plugin = {},
			$window = $(window),
			$document = $(document);

		plugin.onReady = function()
		{
			var namespace = '<?php echo esc_js(__NAMESPACE__); ?>',
				namespaceSlug = '<?php echo esc_js($plugin->slug); ?>',
				qvPrefix = '<?php echo esc_js($plugin->qv_prefix); ?>',
				vars = {
					pluginUrl   : '<?php echo esc_js(rtrim($plugin->utils_url->to('/'), '/')); ?>',
					ajaxEndpoint: '<?php echo esc_js(home_url('/')); ?>'
				},
				i18n = {}, // No translation needed at this time.

				$footer = $('.' + namespace + '-footer'),

				$meta = $footer.find('> .-meta'),

				$metaPopularityTags = $meta.find('> .-popularity-tags'),
				$metaPopularityTagsPopularity = $metaPopularityTags.find('> .-popularity'),
				$metaPopularityTagsTags = $metaPopularityTags.find('> .-tags'),

				$metaAuthorPopularity = $meta.find('> .-author-popularity'),
				$metaAuthorPopularityAuthor = $metaAuthorPopularity.find('> .-author'),
				$metaAuthorPopularityPopularity = $metaAuthorPopularity.find('> .-popularity');

			$metaAuthorPopularityPopularity.on('click', function(e)
			{
				var url, $this = $(this),
					postId = $this.data('postId');

				if(!$this.hasClass('-active') && !$this.data('castPopularityVote'))
				{
					url = vars.ajaxEndpoint;
					url += url.indexOf('?') === -1 ? '?' : '&';
					url += encodeURIComponent(namespace + '[cast_popularity_vote_via_ajax]') + '=' + encodeURIComponent(postId);

					$.get(url, function(data) // Attempt to cast vote and update popularity counter.
					{
						$metaPopularityTagsPopularity.html(Number($metaPopularityTagsPopularity.text()) + Number(data)),
							$this.data('castPopularityVote', 1);
					});
				}
				$this.toggleClass('-active');
			});
		};
		$document.ready(plugin.onReady);
	})(jQuery);
</script>