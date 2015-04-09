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
				i18n = {
					tagsSelected    : '<?php echo esc_js(__('Tags Selected', $plugin->text_domain)); ?>',
					selectedTagsNone: '<?php echo esc_js(__('None', $plugin->text_domain)); ?>',
					selectSomeTags  : '<?php echo esc_js(__('(select some tags) and click `filter by tags`', $plugin->text_domain)); ?>'
				},
				$list = $('.' + namespace + '-list'),
				$listSearchBox = $('.' + namespace + '-list-search-box'),

				$listSearchBoxForm = $listSearchBox.find('> form'),
				$listSearchBoxFormQ = $listSearchBoxForm.find('> .-q'),
				$listSearchBoxFormClear = $listSearchBoxForm.find('> .-clear'),
				$listSearchBoxFormSubmit = $listSearchBoxForm.find('> .-submit'),

				$navigationTabs = $list.find('> .-navigation > .-tabs'),

				$navigationTabsList = $navigationTabs.find('> .-list'),
				$navigationTabsListItems = $navigationTabsList.find('> li'),
				$navigationTabsListItemAnchors = $navigationTabsListItems.find('> a'),

				$navigationTags = $list.find('> .-navigation > .-tags'),

				$navigationTagsFilter = $navigationTags.find('> .-filter'),
				$navigationTagsFilterAnchor = $navigationTagsFilter.find('> a'),

				$navigationTagsOverlay = $navigationTags.find('> .-overlay'),
				$navigationTagsOverlaySelected = $navigationTagsOverlay.find('> .-selected'),
				$navigationTagsOverlayList = $navigationTagsOverlay.find('> .-list'),
				$navigationTagsOverlayListItems = $navigationTagsOverlayList.find('> li'),
				$navigationTagsOverlayListItemAnchors = $navigationTagsOverlayListItems.find('> a'),
				$navigationTagsOverlayButton = $navigationTagsOverlay.find(' > .-button'),

				$clickPageAnchors = $list.find('a[data-click-page]'),
				$clickOrderbyAnchors = $list.find('a[data-click-orderby]'),
				$clickAuthorAnchors = $list.find('a[data-click-author]'),
				$clickCategoryAnchors = $list.find('a[data-click-category]'),
				$clickTagAnchors = $list.find('a[data-click-tag]'),
				$clickQAnchors = $list.find('a[data-click-q]'),

				$attr = $list.find('> .-hidden > .-attr'),
				$attrPage = $list.find('> .-hidden > .-attr-page'),
				$attrOrderby = $list.find('> .-hidden > .-attr-orderby'),
				$attrAuthor = $list.find('> .-hidden > .-attr-author'),
				$attrCategory = $list.find('> .-hidden > .-attr-category'),
				$attrTag = $list.find('> .-hidden > .-attr-tag'),
				$attrQ = $list.find('> .-hidden > .-attr-q');
			/*
			 Functions/handlers.
			 */
			var reload = function(qvs, qvsOnly)
			{
				qvs = qvs || {}; // Force object value.

				var url, attrRaw = $attr.data('attr'),
					requestAttrs = {}, _prop;

				if($navigationTagsFilterAnchor.hasClass('-active'))
					$navigationTagsFilterAnchor.removeClass('-active'),
						$navigationTagsOverlay.hide();

				requestAttrs.page = $attrPage.data('attr');
				requestAttrs.orderby = $attrOrderby.data('attr');
				requestAttrs.author = $attrAuthor.data('attr');
				requestAttrs.category = $attrCategory.data('attr');
				requestAttrs.tag = $attrTag.data('attr');
				requestAttrs.q = $attrQ.data('attr');

				if(!qvs.hasOwnProperty('page'))
					qvs.page = 1; // Page one.

				$.extend(requestAttrs, qvs); // Merge query vars.

				if(qvsOnly) // Using query vars only in this case?
					for(_prop in requestAttrs) // Iterate request attributes.
						if(requestAttrs.hasOwnProperty(_prop) && _prop !== 'page' && !qvs.hasOwnProperty(_prop))
							requestAttrs[_prop] = ''; // Remove this attribute; it's not from qvs.

				$listSearchBoxFormQ.val(requestAttrs.q), // Sync search box.
					$listSearchBoxFormClear[requestAttrs.q ? 'show' : 'hide']();

				url = vars.ajaxEndpoint; // Initialize endpoint URL.
				url += url.indexOf('?') === -1 ? '?zcAC=1' : '&zcAC=1';
				url += '&' + encodeURIComponent(namespace + '[sc_list_via_ajax]') + '=' + encodeURIComponent(attrRaw);

				for(_prop in requestAttrs) if(requestAttrs.hasOwnProperty(_prop))
					url += '&' + encodeURIComponent(qvPrefix + _prop) + '=' + encodeURIComponent(requestAttrs[_prop]);

				$list.css({opacity: 0.5}), $.get(url, function(data)
				{
					$list.replaceWith(data); // Use new list/results.
					scrollTo($('.' + namespace + '-list').offset().top, 0);
					plugin.onReady(); // Attach new event handler.
				});
			};
			/*
			 Search box handlers.
			 */
			$listSearchBoxForm.off('submit.' + namespace),
				$listSearchBoxForm.on('submit.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();
				});
			$listSearchBoxFormQ.off('keydown.' + namespace),
				$listSearchBoxFormQ.on('keydown.' + namespace, function(e)
				{
					if(e.which !== 13)
						return; // Not applicable.

					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						qvsOnly = true; // Always.

					reload({q: $.trim($this.val())}, qvsOnly);
				});
			$listSearchBoxFormClear.off('click.' + namespace),
				$listSearchBoxFormClear.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						qvsOnly = false; // Never.

					reload({q: ''}, qvsOnly);
				});
			$listSearchBoxFormSubmit.off('click.' + namespace),
				$listSearchBoxFormSubmit.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						qvsOnly = true; // Always.

					reload({q: $.trim($listSearchBoxFormQ.val())}, qvsOnly);
				});
			/*
			 Navigation handlers.
			 */
			$navigationTabsListItemAnchors.off('click.' + namespace),
				$navigationTabsListItemAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						qvsOnly = $this.hasClass('-active');

					$navigationTabsListItemAnchors.removeClass('-active'),
						$this.addClass('-active'); // Make active.

					reload({category: $this.data('category')}, qvsOnly);
				});
			$navigationTagsFilterAnchor.off('click.' + namespace),
				$navigationTagsFilterAnchor.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this);

					if($this.hasClass('-active'))
					{
						$this.removeClass('-active');
						$navigationTagsOverlay.fadeOut({duration: 100});
					}
					else // Show it now.
					{
						$this.addClass('-active');
						$navigationTagsOverlay.fadeIn({duration: 100});
					}
				});
			$navigationTagsOverlayListItemAnchors.off('click.' + namespace),
				$navigationTagsOverlayListItemAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						selected = '<i class="fa fa-tags"></i>' +
						           ' <strong>' + i18n.tagsSelected + ':</strong>',
						selectedTags = ''; // Initialize.

					$this.toggleClass('-active');

					$navigationTagsOverlayListItemAnchors
						.each(function()
						      {
							      var $this = $(this);
							      if($this.hasClass('-active'))
								      selectedTags += (selectedTags ? ', ' : '') + $this.text();
						      });
					if(!selectedTags) // No tags selected currently?
						selectedTags = '<strong>' + i18n.selectedTagsNone + '</strong> ' + i18n.selectSomeTags;

					$navigationTagsOverlaySelected.html(selected + ' ' + selectedTags);
				});
			$navigationTagsOverlayButton.off('click.' + namespace),
				$navigationTagsOverlayButton.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var qvsOnly = false, // Change tag(s) only.
						tags = ''; // Initialize list of tags.

					$navigationTagsOverlayListItemAnchors
						.each(function()
						      {
							      var $this = $(this);
							      if($this.hasClass('-active'))
								      tags += (tags ? ',' : '') + $this.data('tag');
						      });
					reload({tag: tags}, qvsOnly); // Reload the list now.
				});
			/*
			 Click handlers.
			 */
			$clickPageAnchors.off('click.' + namespace),
				$clickPageAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = false; // Never on paging.

					reload({page: $this.data('clickPage')}, qvsOnly);
				});
			$clickOrderbyAnchors.off('click.' + namespace),
				$clickOrderbyAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = false; // Never on ordering.

					reload({orderby: $this.data('clickOrderby')}, qvsOnly);
				});
			$clickAuthorAnchors.off('click.' + namespace),
				$clickAuthorAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = isClear ? false : true;

					reload({author: $this.data('clickAuthor')}, qvsOnly);
				});
			$clickCategoryAnchors.off('click.' + namespace),
				$clickCategoryAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = isClear ? false : true;

					reload({category: $this.data('clickCategory')}, qvsOnly);
				});
			$clickTagAnchors.off('click.' + namespace),
				$clickTagAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = isClear ? false : true;

					reload({tag: $this.data('clickTag')}, qvsOnly);
				});
			$clickQAnchors.off('click.' + namespace),
				$clickQAnchors.on('click.' + namespace, function(e)
				{
					e.preventDefault();
					e.stopImmediatePropagation();

					var $this = $(this),
						isClear = $this.hasClass('-clear'),
						qvsOnly = isClear ? false : true;

					reload({q: $this.data('clickQ')}, qvsOnly);
				});
		};
		$document.ready(plugin.onReady);
	})(jQuery);
</script>
