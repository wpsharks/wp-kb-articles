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

			vars = window[namespace + '_list_vars'], i18n = window[namespace + '_list_i18n'];
	};
	$document.ready(plugin.onReady); // DOM ready handler.
})(jQuery);