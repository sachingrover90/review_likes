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