(function ($, Drupal) {
  Drupal.behaviors.deleteProduct = {
    attach: function (context, settings) {
      let nidToDelete = null;

      // Open modal when delete link clicked
      $(document).on('click', '.delete-tester', function (e) {
        e.preventDefault();
        nidToDelete = $(this).data('nid');
        console.log("🟢 Clicked Delete for Node ID:", nidToDelete);
        $('#deletegiftModal').modal('show');
      });

      // Confirm delete
      $(document).on('click', '#confirmDeleteBtn', function () {
        console.log("🟢 Confirm delete for Node ID:", nidToDelete);

        if (nidToDelete) {
          $.ajax({
            url: Drupal.url('tester/delete-profile/' + nidToDelete),
            type: 'POST',
            data: { _drupal_ajax: true },
            success: function () {
              console.log("✅ Node deleted successfully:", nidToDelete);
              $('#deletegiftModal').modal('hide');
              location.reload(); // reload list after deletion
            },
            error: function () {
              console.error("❌ Error deleting product:", nidToDelete);
              alert('Error deleting product.');
            }
          });
        }
      });
    }
  };
})(jQuery, Drupal);

(function($, Drupal, drupalSettings) {
  Drupal.behaviors.productCategoryOther = {
    attach: function(context, settings) {
      const configs = [
        // Radio groups with wrapper
        { radio: 'languages_known', field: 'edit-other-language', value: '30', hasWrapper: false  },
       
        
        
      ];

      function toggleOtherField() {
        configs.forEach(config => {
          const selectedValue = $(`input[name="${config.radio}"]:checked`, context).val();
          const $field = $(`#${config.field}`, context);
          
          if (config.hasWrapper) {
            const $wrapper = $field.closest('.js-form-wrapper, .form-item');
            if (selectedValue == config.value) {
              $wrapper.slideDown(300);
              $field.attr('required', 'required');
            } else {
              $wrapper.slideUp(300);
              $field.removeAttr('required');
              $field.val('');
            }
          } else {
            const $label = $(`label[for="${config.field}"]`, context);
            const $description = $(`#${config.field}--description`, context);
            
            if (selectedValue == config.value) {
              $field.stop(true, true).slideDown(300);
              $label.stop(true, true).slideDown(300);
              if ($description.length) $description.stop(true, true).slideDown(300);
              $field.attr('required', 'required');
            } else {
              $field.stop(true, true).slideUp(300);
              $label.stop(true, true).slideUp(300);
              if ($description.length) $description.stop(true, true).slideUp(300);
              $field.removeAttr('required');
              $field.val('');
            }
          }
        });
      }

      // Hide all fields initially
      configs.forEach(config => {
        const $field = $(`#${config.field}`, context);
        
        if (config.hasWrapper) {
          $field.closest('.js-form-wrapper, .form-item').hide();
        } else {
          $field.hide();
          $(`label[for="${config.field}"]`, context).hide();
          $(`#${config.field}--description`, context).hide();
        }
      });

      // Initialize and add event listeners
      toggleOtherField();
      
      const radioSelectors = configs.map(config => `input[name="${config.radio}"]`).join(', ');
      $(radioSelectors, context).on('change', toggleOtherField);
    }
  };
})(jQuery, Drupal, drupalSettings);