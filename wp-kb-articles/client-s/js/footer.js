(function($)
{
	'use strict';

	var plugin = {},
		$window = $(window),
		$document = $(document);

	plugin.onReady = function()
	{
		var namespace = 'wp_kb_articles',
			namespaceSlug = 'wp-kb-articles',
			vars = window[namespace + '_footer_vars'],
			i18n = window[namespace + '_footer_i18n'],
			qvPrefix = 'kb_', // Query vars.

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
				url += encodeURIComponent(namespace + '[cast_popularity_vote]') + '=' + encodeURIComponent(postId);

				$.get(url, function(data) // Attempt to cast vote and update popularity counter.
				{
					$metaPopularityTagsPopularity.html(Number($metaPopularityTagsPopularity.text()) + Number(data)),
						$this.data('castPopularityVote', 1);
				});
			}
			$this.toggleClass('-active');
		});
	};
	$document.ready(plugin.onReady); // DOM ready handler.
})(jQuery);