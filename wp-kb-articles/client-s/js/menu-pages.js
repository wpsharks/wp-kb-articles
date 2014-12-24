(function($)
{
	'use strict';

	var plugin = {},
		$window = $(window),
		$document = $(document);

	plugin.onReady = function() // jQuery DOM ready event handler.
	{
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific selectors needed by routines below.
		 ------------------------------------------------------------------------------------------------------------ */

		var namespace = 'wp_kb_articles',
			namespaceSlug = 'wp-kb-articles',

			$menuPage = $('.' + namespaceSlug + '-menu-page'),
			$menuPageArea = $('.' + namespaceSlug + '-menu-page-area'),

			vars = window[namespace + '_vars'], i18n = window[namespace + '_i18n'],

			codeMirrors = [], cmOptions = {
				lineNumbers  : false,
				matchBrackets: true,
				theme        : 'ambiance',
				tabSize      : 3, indentWithTabs: true,
				extraKeys    : {
					'F11': function(cm)
					{
						if(cm.getOption('fullScreen'))
							cm.setOption('fullScreen', false),
								$('#adminmenuwrap, #wpadminbar').show();

						else cm.setOption('fullScreen', true),
							$('#adminmenuwrap, #wpadminbar').hide();
					}
				}
			};
		/* ------------------------------------------------------------------------------------------------------------
		 Plugin-specific JS for any menu page area of the dashboard.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPageArea.find('.pmp-tabs > a').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this),
				$tabs = $this.parent().find('> a'),
				$tabPanesDiv = $this.parent().next('.pmp-tab-panes'),
				$tabPanes = $tabPanesDiv.children(), // All tab panes.
				$targetTabPane = $tabPanesDiv.find('> ' + $this.data('target'));

			$tabs.add($tabPanes).removeClass('pmp-active'), $tabPanes.hide(),
				$this.add($targetTabPane).addClass('pmp-active'), $targetTabPane.show();
		});
		$menuPageArea.find('[data-pmp-action]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), data = $this.data();
			if(typeof data.pmpConfirmation !== 'string' || confirm(data.pmpConfirmation))
				location.href = data.pmpAction;
		});
		$menuPageArea.find('[data-toggle~="select-all"]').on('click', function()
		{
			$(this).select(); // jQuery makes this easy for us.
		});
		$menuPageArea.find('[data-toggle~="alert"]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			var $this = $(this), $closestBlock = $this.closest('table,div'),
				alertMarkup = $this.data('alert'), // The content for this alert.
				$modalDialogOverlay = $('<div class="pmp-modal-dialog-overlay"></div>'),
				$modalDialog = $('<div class="pmp-modal-dialog">' +
				                 '   <a class="pmp-modal-dialog-close"></a>' +
				                 '   ' + alertMarkup +
				                 '</div>');
			$closestBlock.after($modalDialogOverlay).after($modalDialog),
				$modalDialogOverlay.add($modalDialog.find('> .pmp-modal-dialog-close')).on('click', function(e)
				{
					e.preventDefault(), e.stopImmediatePropagation(),
						$menuPageArea.find('.pmp-modal-dialog').remove(),
						$menuPageArea.find('.pmp-modal-dialog-overlay').remove();
				});
		});
		/* ------------------------------------------------------------------------------------------------------------
		 JS for an actual/standard plugin menu page; e.g. options.
		 ------------------------------------------------------------------------------------------------------------ */

		$menuPage.find('[data-cm-mode]')
			.each(function() // CodeMirrors.
			      {
				      var $this = $(this),
					      cmMode = $this.data('cmMode'),
					      cmHeight = $this.data('cmHeight'),
					      $textarea = $this.find('textarea');

				      if($textarea.length !== 1) return; // Invalid markup.

				      window.CodeMirror = CodeMirror || {fromTextArea: function(){}};

				      $this.addClass('cm'), // See `menu-pages.css` to customize styles.
					      codeMirrors.push(CodeMirror.fromTextArea($textarea[0], $.extend({}, cmOptions, {mode: cmMode}))),
					      codeMirrors[codeMirrors.length - 1].setSize(null, cmHeight);
			      });
		var refreshCodeMirrors = function(/* Refresh CodeMirrors. */)
		{
			$.each(codeMirrors, function(i, codeMirror){ codeMirror.refresh(); });
		};
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('[data-toggle~="other"]').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$($(this).data('other')).toggle(), // Toggle another.
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panels-open').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').addClass('open')
				.next('.pmp-panel-body').addClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panels-close').on('click', function()
		{
			$menuPage.find('.pmp-panel-heading').removeClass('open')
				.next('.pmp-panel-body').removeClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-panel-heading').on('click', function(e)
		{
			e.preventDefault(), e.stopImmediatePropagation();

			$(this).toggleClass('open') // Toggle this panel now.
				.next('.pmp-panel-body').toggleClass('open'),
				refreshCodeMirrors(); // Refresh CodeMirrors also.
		});
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */

		$menuPage.find('.pmp-if-change').on('change', function()
		{
			var $this = $(this),
				thisValue = $.trim($this.val()),
				$thisPanel = $this.closest('.pmp-panel'),
				$thisNest = $this.closest('.pmp-if-nest'),
				$thisContainer = $thisPanel; // Default container.
			if($thisNest.length) $thisContainer = $thisNest;
			var matchValue = $this.hasClass('pmp-if-value-match');

			var enabled = thisValue !== '' && thisValue !== '0',
				disabled = !enabled; // The opposite.

			var ifEnabled = '.pmp-if-enabled',
				ifEnabledShow = '.pmp-if-enabled-show',

				ifDisabled = '.pmp-if-disabled',
				ifDisabledShow = '.pmp-if-disabled-show';

			var withinANest = function() // Prevents nest conflicts.
			{
				if($thisNest.length) return false; // Inside a nest already.
				// In this case, the set of matches has already been filtered above;
				// i.e. by restricting the context itself to the closest `.pmp-if-nest`.

				// If we are NOT inside a nest, do not include anything that is.
				return $(this).hasClass('pmp-in-if-nest'); // Class for optimization.
			};
			var valueMatches = function() // Matches current value; if applicable.
			{
				if(!matchValue) return true; // Not matching current value.
				// In this case we want to include everything; i.e. do not exclude.

				// Otherwise, we only include this if it has a matching value.
				return $(this).hasClass('pmp-if-value-' + thisValue);
			};
			if(enabled) // If enabled; show and enable all input fields.
				$thisContainer.find(ifEnabled + ',' + ifEnabledShow).not(withinANest).filter(valueMatches)
					.show().css('opacity', 1).find(':input').removeAttr('disabled');

			else // We use opacity to conceal; and hide if applicable.
			{
				$thisContainer.find(ifEnabled + ',' + ifEnabledShow).not(withinANest)
					.css('opacity', 0.2).find(':input').attr('disabled', 'disabled'),
					$thisContainer.find(ifEnabledShow).not(withinANest).hide();
			}
			if(disabled) // If disabled; show and enable all input fields.
				$thisContainer.find(ifDisabled + ',' + ifDisabledShow).not(withinANest)
					.show().css('opacity', 1).find(':input').removeAttr('disabled');

			else // We use opacity to conceal; and hide if applicable.
			{
				$thisContainer.find(ifDisabled + ',' + ifDisabledShow).not(withinANest)
					.css('opacity', 0.2).find(':input').attr('disabled', 'disabled'),
					$thisContainer.find(ifDisabledShow).not(withinANest).hide();
			}
			refreshCodeMirrors(); // Refresh CodeMirrors also.
		})
			.trigger('change'); // Initialize.
		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -  */
	};
	$document.ready(plugin.onReady); // DOM ready handler.
})(jQuery);