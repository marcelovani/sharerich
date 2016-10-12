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

    })

})(jQuery, Drupal, drupalSettings);
