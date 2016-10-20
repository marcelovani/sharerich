/**
 * @file
 * Sharerich.
 */

(function ($, Drupal, drupalSettings) {

	'use strict';

	$(document).ready(function () {

		// Reset button.
		$('.sharerich-form .button.reset').click(function () {
			var parent = $(this).parent();
			var grand_parent = parent.parent();
			var markup = parent.find('.markup');
			var default_markup = grand_parent.find('.default-markup');
			markup.val(default_markup.val());
		});

		// Sticky.
		$(window).scroll(function () {
			var target = $('.sharerich-wrapper.vertical.sticky');
			// Find the parent container.
			var container = target.parent();
			if (container.length) {
				if ($(window).scrollTop() > container.offset().top)
					target.addClass('stick');
				else
					target.removeClass('stick');
			}
		});

	})

})(jQuery, Drupal, drupalSettings);
