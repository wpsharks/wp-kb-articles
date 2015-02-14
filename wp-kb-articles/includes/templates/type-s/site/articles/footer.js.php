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
					ajaxEndpoint: '<?php echo esc_js(home_url('/')); ?>',
					postId      : '<?php echo esc_js(get_the_ID()); ?>'
				},
				i18n = {}, // No translation needed at this time.

				$footer = $('.' + namespace + '-footer'),

				$meta = $footer.find('> .-meta'),

				$metaPopularityTagsFeedback = $meta.find('> .-popularity-tags-feedback'),
				$metaPopularityTagsFeedbackPopularity = $metaPopularityTagsFeedback.find('> .-popularity'),
				$metaPopularityTagsFeedbackTags = $metaPopularityTagsFeedback.find('> .-tags'),
				$metaPopularityTagsFeedbackFeedback = $metaPopularityTagsFeedback.find('> .-feedback'),

				$metaAuthorPopularity = $meta.find('> .-author-popularity'),
				$metaAuthorPopularityAuthor = $metaAuthorPopularity.find('> .-author'),
				$metaAuthorPopularityPopularity = $metaAuthorPopularity.find('> .-popularity');

			$metaAuthorPopularityPopularity.on('click', function(e)
			{
				e.preventDefault();
				e.stopImmediatePropagation();

				var url, $this = $(this);

				if(!$this.hasClass('-active') && !$this.data('castPopularityVote'))
				{
					url = vars.ajaxEndpoint;
					url += url.indexOf('?') === -1 ? '?' : '&';
					url += encodeURIComponent(namespace + '[cast_popularity_vote_via_ajax]') + '=' + encodeURIComponent(vars.postId);

					$.get(url, function(data) // Attempt to cast vote and update popularity counter.
					{
						$metaPopularityTagsFeedbackPopularity.html(Number($metaPopularityTagsFeedbackPopularity.text()) + Number(data)),
							$this.data('castPopularityVote', 1);
					});
				}
				$this.toggleClass('-active');
			});

			(function() // Record stats.
			{
				var url, $this = $(this);

				url = vars.ajaxEndpoint;
				url += url.indexOf('?') === -1 ? '?' : '&';
				url += encodeURIComponent(namespace + '[record_stats_via_ajax]') + '=' + encodeURIComponent(vars.postId);

				$.get(url, function(data){}); // Attempt to record statistics.
			})();
		};
		$document.ready(plugin.onReady);
	})(jQuery);
</script>