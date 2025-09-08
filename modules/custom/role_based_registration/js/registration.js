(function($, Drupal) {
  Drupal.behaviors.roleBasedRegistration = {
    attach: function(context, settings) {
      // Update checkboxes when select changes
      $('#custom-role-select', context).once('role-select').change(function() {
        var role = $(this).val();
        // Uncheck all role checkboxes
        $('input[name^="roles["]').prop('checked', false);
        // Check the selected one
        if (role) {
          $('#edit-roles-' + role).prop('checked', true);
        }
      });
      
      // Update select when checkboxes change
      $('input[name^="roles["]', context).once('role-checkbox').change(function() {
        if ($(this).is(':checked') && $(this).val() !== 'authenticated') {
          $('#custom-role-select').val($(this).val());
        }
      });
    }
  };
})(jQuery, Drupal);


(function($, Drupal, once) {
  'use strict';

  Drupal.behaviors.productFormPlatforms = {
    attach: function(context, settings) {
      // Add platform field
      $(once('add-platform', '#addPlatformBtn', context)).on('click', function(e) {
        e.preventDefault();
        var platformCount = $('.platform-wrapper', context).length;
        
        if (platformCount < 20) {
          // Clone the first platform wrapper and clear its values
          var $firstPlatform = $('.platform-wrapper:first', context);
          var $newPlatform = $firstPlatform.clone();
          
          // Update IDs and clear values
          var newId = platformCount + 1;
          $newPlatform.attr('id', 'adminplatformBlock-' + newId);
          $newPlatform.find('input').val('').attr('id', function(i, id) {
            return id.replace(/\d+$/, newId);
          });
          
          // Add remove button
          $newPlatform.find('.remove-btn').remove();
          $newPlatform.find('.storelink-section').append(
            '<button type="button" class="btn danger remove-btn" data-delta="' + newId + '">Remove</button>'
          );
          
          // Append to container
          $('#adminplatform', context).append($newPlatform);
        } else {
          alert(Drupal.t('Maximum 20 platform links allowed.'));
        }
      });

      // Remove platform field
      $(context).on('click', '.remove-btn', function(e) {
        e.preventDefault();
        var delta = $(this).data('delta');
        $('#adminplatformBlock-' + delta, context).remove();
      });
    }
  };

})(jQuery, Drupal, once);