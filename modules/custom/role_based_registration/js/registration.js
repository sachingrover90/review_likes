(function ($, Drupal) {
  Drupal.behaviors.registrationForm = {
    attach: function (context, settings) {
      // Apply once to avoid multiple bindings after AJAX
      once('role-style', '#edit-role', context).forEach(function (el) {
        $(el).css('border-color', 'blue');

        $(el).on('change', function () {
          // Smooth scroll to role fields when role is selected
          var $target = $('#role-fields-wrapper', context);
          if ($target.length) {
            $('html, body').animate({
              scrollTop: $target.offset().top - 100 // small offset for spacing
            }, 500, function () {
              $target.attr('tabindex', -1).focus();
            });
          }
        });
      });

      // Optional: Animate any AJAX-add buttons
      once('ajax-add-buttons', '.form-submit', context).forEach(function (button) {
        $(button).on('click', function () {
          $(button).addClass('loading');
          setTimeout(function () {
            $(button).removeClass('loading');
          }, 1000);
        });
      });
    }
  };
})(jQuery, Drupal);

