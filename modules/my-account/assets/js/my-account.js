(function ($) {
  const myAccountActions = {
    /**
     * Init function execute the functionality.
     */
    init() {
      $(document.body).on(
        "click",
        ".trigger_update_address",
        myAccountActions.open_edit_address_popup
      );
      $(document.body).on(
        "click",
        ".cancel-update-order-address, .edit-address-popup--close-btn",
        myAccountActions.close_edit_address_popup
      );

      // Add button to edit the address.
      myAccountActions.add_edit_address_button();

      $(document.body).on(
        "click",
        ".edit-address-popup--load-address",
        myAccountActions.load_billing_address
      );
    },

    /**
     * Function to hide/show the popup to edit the billing address.
     *
     * @param {event} event
     */
    open_edit_address_popup(event) {
      event.preventDefault();
      if ($(".edit-address-popup--wrapper").hasClass("show")) {
        $(".edit-address-popup--wrapper").removeClass("show");
      } else {
        $(".edit-address-popup--wrapper").addClass("show");
      }
    },
    /**
     * Function to hide the address popup on the click of a button.
     *
     * @param {event} event
     */
    close_edit_address_popup(event) {
      event.preventDefault();
      $(".edit-address-popup--wrapper").removeClass("show");
    },
    /**
     * Function to display the edit address button when the page is loaded.
     */
    add_edit_address_button() {
      $(
        ".woocommerce-MyAccount-content .woocommerce-customer-details .woocommerce-column__title"
      ).append('<a href="#!" class="trigger_update_address">Edit</a>');
    },
    /**
     * Ajax call to load the billing address from the customer.
     */
    load_billing_address() {
      // Get user ID to load data for
      const user_id = $("#customer_user_id").val();
      const pdf_iewa_customer_nonce = $(
        "#pdf_iewa_get_customer_details_nonce"
      ).val();

      if (!user_id) {
        window.alert("User ID not found.");
        return false;
      }

      const data = {
        user_id,
        action: "pdf_iewa_get_customer_details",
        security: pdf_iewa_customer_nonce,
      };

      $(".edit-address-popup--content .woocommerce").block({
        message: null,
        overlayCSS: {
          background: "#fff",
          opacity: 0.6,
        },
      });

      $.ajax({
        url: woocommerce_params.ajax_url,
        data,
        type: "POST",
        success(response) {
          if (response.success && response.data.billing) {
            $.each(response.data.billing, function (key, value) {
              console.log("#billing_" + key + " : " + value);
              $("#_billing_" + key).val(value);
            });
          } else {
            window.alert(response.data.message);
          }
          $(".edit-address-popup--content .woocommerce").unblock();
        },
      });
    },
  };

  $(function () {
    myAccountActions.init();
  });
})(jQuery);
