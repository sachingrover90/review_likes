(function ($, Drupal) {
  Drupal.behaviors.deleteProduct = {
    attach: function (context, settings) {
      let nidToDelete = null;

      // Open modal when delete link clicked
      $(document).on('click', '.delete-claim-gift', function (e) {
        e.preventDefault();
        nidToDelete = $(this).data('nid');
        // console.log("🟢 Clicked Delete for Node ID:", nidToDelete);
        $('#deletegiftModal').modal('show');
      });

      // Confirm delete
      $(document).on('click', '#confirmDeleteBtn', function () {
        // console.log("🟢 Confirm delete for Node ID:", nidToDelete);

        if (nidToDelete) {
                   $.ajax({
            url: Drupal.url('claims-gifts/delete-node/' + nidToDelete),
            type: 'POST',
            data: { _drupal_ajax: true },
            success: function () {
              // console.log("✅ Node deleted successfully:", nidToDelete);
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


(function($, Drupal) {
  'use strict';

  Drupal.behaviors.couponToggle = {
    attach: function(context, settings) {
      // console.log("=== COUPON TOGGLE DEBUG ===");
      
      // Use context to ensure we're searching in the right scope
      // Look for the checkbox by its name or class
      const toggle = context.querySelector('input[name="coupon_toggle"]') || 
                    context.querySelector('.couponToggle') ||
                    document.querySelector('input[name="coupon_toggle"]');
      
      const field = context.getElementById('paffliatelink') || 
                   document.getElementById('paffliatelink');
        
      // console.log("Toggle found:", toggle);
      // console.log("Field found:", field);
      
      if (!toggle) {
        console.error("Toggle checkbox not found! Searching for:");
        // console.log("input[name='coupon_toggle']:", context.querySelector('input[name="coupon_toggle"]'));
        // console.log(".couponToggle:", context.querySelector('.couponToggle'));
        return;
      }
      
      if (!field) {
        console.error("Field not found!");
        return;
      }

      function updateFieldState() {
        const isChecked = toggle.checked;
        // console.log("Toggle state changed:", isChecked);
        
        // Toggle field properties
        field.disabled = !isChecked;
        field.required = isChecked;
        
        // Visual feedback
        if (isChecked) {
          field.style.backgroundColor = "#ffffff";
          field.style.opacity = "1";
          field.placeholder = "Enter coupon or affiliate link";
          field.removeAttribute('readonly');
        } else {
          field.style.backgroundColor = "#f8f9fa";
          field.style.opacity = "0.6";
          field.placeholder = "Enable toggle to enter coupon link";
          field.value = '';
        }
      }

      // Initialize
      // console.log("Initial toggle state:", toggle.checked);
      updateFieldState();
      
      // Add event listeners
      toggle.addEventListener('change', updateFieldState);
      toggle.addEventListener('click', updateFieldState);
      
      // Also handle slider click
      const slider = context.querySelector('.slider') || document.querySelector('.slider');
      if (slider) {
        slider.addEventListener('click', function(e) {
          e.preventDefault();
          toggle.checked = !toggle.checked;
          const event = new Event('change', { bubbles: true });
          toggle.dispatchEvent(event);
        });
      }
      
      // console.log("Coupon toggle initialized successfully");
    }
  };

})(jQuery, Drupal);

(function ($, Drupal) {
  Drupal.behaviors.toggleWrapper = {
    attach: function (context, settings) {
      // console.log('here i am..');
      $(".toggle-wrapper.mt-3", context).wrapInner('<label class="toggle-switch"></label>');
    }
  };
})(jQuery, Drupal);
document.getElementById('edit-order-id').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});
document.getElementById('edit-shipping-zip').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});

document.getElementById('edit-user-phone').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '');
});