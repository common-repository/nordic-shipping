(function ($) {
  "use strict";

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */
  $(".shipit_service_price_div").hide();
  $(document).ready(function () {
    $(document).on("click", ".uniwin_close_price_model", function (e) {
      location.reload();
    });

    $.validator.setDefaults({
      errorClass: "help-block",
      highlight: function (element) {
        $(element).closest(".form-group").addClass("has-error");
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element)
          .closest(".form-group")
          .removeClass("has-error")
          .addClass("has-success");
      },
    });
    $("#shipit_uniwin_admin_settings").validate({
      errorElement: "td",
      errorClass: "help-block",
      focusInvalid: false,
      ignore: [],
      rules: {
        unishipit_api_key: {
          required: true,
        },
        unishipit_api_secret: {
          required: true,
        },
        unishipit_sender_name: {
          required: true,
          minlength: 3,
        },
        unishipit_sender_street: {
          required: true,
          minlength: 4,
        },
        unishipit_sender_city: {
          required: true,
          minlength: 4,
        },
        unishipit_sender_country: {
          required: true,
        },
        unishipit_sender_postcode: {
          required: true,
          digits: true,
        },
        unishipit_sender_vat: {
          required: true,
          digits: true,
        },
        unishipit_sender_email: {
          required: true,
          email: true,
        },
        unishipit_sender_phone: {
          required: true,
          digits: true,
        },
        unishipit_sender_contents: {
          required: true,
        },
      },
      messages: {
        unishipit_api_key: {
          required: "Please enter api key.",
        },
        unishipit_api_secret: {
          required: "Please enter secret key.",
        },
        unishipit_sender_name: {
          required: "Please enter sender name.",
        },
        unishipit_sender_name: {
          required: "Please enter sender name.",
          minlength: "Please enter at least 3 characters.",
        },
        unishipit_sender_street: {
          required: "Please enter street address.",
          minlength: "Please enter at least 4 characters.",
        },
        unishipit_sender_city: {
          required: "Please enter city.",
          minlength: "Please enter at least 4 characters.",
        },
        unishipit_sender_country: {
          required: "Please enter country.",
        },
        unishipit_sender_postcode: {
          required: "Please enter postal code.",
          digits: "Numbers only allowed.",
        },
        unishipit_sender_vat: {
          required: "Please enter vat id.",
          digits: "Numbers only allowed.",
        },
        unishipit_sender_email: {
          required: "Please enter email.",
          email: "Invalid email id.",
        },
        unishipit_sender_phone: {
          required: "Please enter phone number.",
          digits: "Numbers only allowed.",
        },
        unishipit_sender_contents: {
          required: "Please enter contents.",
        },
      },
      invalidHandler: function (event, validator) {
        //display error alert on form submit
        $(".alert-danger", $(".login-form")).show();
        $(".help-block").show();
      },

      highlight: function (e) {
        $(e).closest(".form-group").removeClass("has-info").addClass("error");
        $(".help-block").show();
      },
      unhighlight: function (element) {
        // <-- fires when element is valid
        $(element)
          .closest(".form-group")
          .removeClass("error")
          .addClass("has-info");
        $(element).closest(".form-group").find(".help-block").remove();
      },

      success: function (e) {
        $(e).closest(".form-group").removeClass("error").addClass("info");
        $(e).remove();
      },

      errorPlacement: function (error, element) {
        error.insertAfter(element.parent());
      },

      submitHandler: function (form) {
        form.submit();
        $("button[type=submit], input[type=submit]").attr("disabled", true);
      },
      invalidHandler: function (form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
          validator.errorList[0].element.focus();
        }
      },
    });

    $(".carrier_agents_assistant").hide();
    $("#unishipit_show_carrier").click(function () {
      if ($(this).is(":checked")) {
        $(".carrier_agents_assistant").show();
      } else {
        $(".carrier_agents_assistant").hide();
      }
    });
    if ($("#unishipit_show_carrier").is(":checked")) {
      $(".carrier_agents_assistant").show();
    }

    $(".unishipit-home-delivery").hide();
    $("#customRadioInline7").click(function () {
      if ($(this).is(":checked")) {
        $(".unishipit-home-delivery").show();
      } else {
        $(".unishipit-home-delivery").hide();
      }
    });
    if ($("#customRadioInline7").is(":checked")) {
      $(".unishipit-home-delivery").show();
    }
    $("#shipit_uniwin_general_settings").validate({
      errorElement: "td",
      errorClass: "help-block",
      focusInvalid: false,
      ignore: [],
      rules: {
        unishipit_width: {
          required: true,
          digits: true,
        },
        unishipit_length: {
          required: true,
          digits: true,
        },
        unishipit_height: {
          required: true,
          digits: true,
        },
        unishipit_parcel: {
          required: true,
          digits: true,
        },
        unishipit_hd_width: {
          required: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
          digits: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
        },
        unishipit_hd_length: {
          required: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
          digits: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
        },
        unishipit_hd_height: {
          required: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
          digits: function () {
            if ($("#customRadioInline7").is(":checked")) {
              return true;
            } else {
              return false;
            }
          },
        },
      },
      messages: {
        unishipit_width: {
          required: "Please enter width.",
          digits: "Numbers only allowed.",
        },
        unishipit_length: {
          required: "Please enter length.",
          digits: "Numbers only allowed.",
        },
        unishipit_height: {
          required: "Please enter height.",
          digits: "Numbers only allowed.",
        },
        unishipit_parcel: {
          required: "Please enter parcel.",
          digits: "Numbers only allowed.",
        },
        unishipit_hd_width: {
          required: "Please enter width for home delivery.",
          digits: "Numbers only allowed.",
        },
        unishipit_hd_length: {
          required: "Please enter length for home delivery.",
          digits: "Numbers only allowed.",
        },
        unishipit_hd_height: {
          required: "Please enter heigth for home delivery.",
          digits: "Numbers only allowed.",
        },
      },
      invalidHandler: function (event, validator) {
        //display error alert on form submit
        $(".alert-danger", $(".login-form")).show();
        $(".help-block").show();
      },

      highlight: function (e) {
        $(e).closest(".form-group").removeClass("has-info").addClass("error");
        $(".help-block").show();
      },
      unhighlight: function (element) {
        // <-- fires when element is valid
        $(element)
          .closest(".form-group")
          .removeClass("error")
          .addClass("has-info");
        $(element).closest(".form-group").find(".help-block").remove();
      },

      success: function (e) {
        $(e).closest(".form-group").removeClass("error").addClass("info");
        $(e).remove();
      },

      errorPlacement: function (error, element) {
        error.insertAfter(element.parent());
      },

      submitHandler: function (form) {
        form.submit();

        $("button[type=submit], input[type=submit]").attr("disabled", true);
        var tabid = $('button[name="gs_save"]').attr("data-tabid");
        console.log(tabid);
        $("#" + $(tabid).attr("id").replace("tab", "content")).addClass(
          "active"
        );
      },
      invalidHandler: function (form, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
          validator.errorList[0].element.focus();
        }
      },
    });

    $("#uniwin_shipit_service_field").change(function () {
      var price = $(this).find(":selected").attr("data-price");
      if (price != "") {
        $(".shipit_service_price_div").show();
        $(".shipit_service_price").text(price);
        $('input[name="shipit_uniwin_service_price"]').val(price);
      }
    });

    $("#open").click(function () {
      $("#a").css("display", "block");
      $("#b").css("display", "block");
      $('#b input[type="text"]').val("");
      $('#b input[type="email"]').val("");
    });

    $.validator.addMethod("checklower", function (value) {
      return /[a-z]/.test(value);
    });
    $.validator.addMethod("checkupper", function (value) {
      return /[A-Z]/.test(value);
    });
    $.validator.addMethod("checkdigit", function (value) {
      return /[0-9]/.test(value);
    });

    $("#new_uniwin_user").click(function () {
      $("#shipit_uniwin_new_user").validate({
        errorElement: "div",
        errorClass: "help-block",
        focusInvalid: false,
        ignore: [],
        rules: {
          reg_uniwin_name: {
            required: true,
            minlength: 3,
          },
          reg_uniwin_email: {
            required: true,
            email: true,
          },
          reg_uniwin_password: {
            required: true,
            minlength: 8,
            maxlength: 12,
            checklower: true,
            checkupper: true,
            checkdigit: true,
          },
          reg_uniwin_phone: {
            required: true,
            digits: true,
          },
          reg_uniwin_address: {
            required: true,
            minlength: 4,
          },
          reg_uniwin_postal: {
            required: true,
            digits: true,
          },
          reg_uniwin_city: {
            required: true,
            minlength: 4,
          },
          reg_uniwin_country: {
            required: true,
          },
          reg_uniwin_vat: {
            required: true,
            digits: true,
          },
        },
        messages: {
          reg_uniwin_name: {
            required: "Please enter name.",
          },
          reg_uniwin_email: {
            required: "Please enter Email id.",
            email: "Invalid email id.",
          },
          reg_uniwin_password: {
            required: "Please enter password.",
            minlength: "Password contains atleast 8 letters",
            maxlength: "Password contains below 12 letters",
            checklower: "Need atleast 1 lowercase alphabet",
            checkupper: "Need atleast 1 uppercase alphabet",
            checkdigit: "Need atleast 1 digit",
          },
          reg_uniwin_phone: {
            required: "Please enter phone number with country code.",
          },
          reg_uniwin_address: {
            required: "Please enter street address.",
            minlength: "Please enter at least 4 characters.",
          },
          reg_uniwin_city: {
            required: "Please enter city.",
            minlength: "Please enter at least 4 characters.",
          },
          reg_uniwin_country: {
            required: "Please select country.",
          },
          reg_uniwin_postal: {
            required: "Please enter postal code.",
            digits: "Numbers only allowed.",
          },
          reg_uniwin_vat: {
            required: "Please enter vat id.",
            digits: "Numbers only allowed.",
          },
        },
        invalidHandler: function (event, validator) {
          $(".help-block").show();
        },

        highlight: function (e) {
          $(e).closest(".form-group").removeClass("has-info").addClass("error");
          $(".help-block").show();
        },
        unhighlight: function (element) {
          // <-- fires when element is valid
          $(element)
            .closest(".form-group")
            .removeClass("error")
            .addClass("has-info");
          $(element).closest(".form-group").find(".help-block").remove();
        },

        success: function (e) {
          $(e).closest(".form-group").removeClass("error").addClass("info");
          $(e).remove();
        },

        errorPlacement: function (error, element) {
          error.insertAfter(element.parent());
        },

        submitHandler: function (form) {
          form.submit();
          $("button[type=submit], input[type=submit]").attr("disabled", true);
        },
        invalidHandler: function (form, validator) {
          var errors = validator.numberOfInvalids();
          if (errors) {
            validator.errorList[0].element.focus();
          }
        },
      });
    });

    $(".cancel").click(function () {
      $("#a").fadeOut();
      $("#b").fadeOut();
    });
  });
  jQuery(document).on("click", ".shipit-uniwin-auto-sync", function () {
    var order_id = jQuery(this).attr("data-id");
    var added = jQuery(this).attr("data-added");
    var from_data = "admin";
    if (added == 1) {
      if (
        confirm(
          "You already created the shipment for this order, need to create again?"
        )
      ) {
        var data = {
          action: "NODS_shipit_sync",
          order_id: order_id,
          from_data: from_data,
        };
        jQuery(this).text("Processing...").prop("disabled", true);
        var thiss = $(this);
        jQuery.post(ajaxurl, data, function (response) {
          if (response == "success") {
            alert("Order sync success");
            location.reload();
          } else {
            alert(response);
           // location.reload();
          }
          thiss.text("Sync").prop("disabled", false);
        });
      } else {
        return false;
      }
    } else {
      var data = {
        action: "NODS_shipit_sync",
        order_id: order_id,
      };
      jQuery(this).text("Processing...").prop("disabled", true);
      var thiss = $(this);
      jQuery.post(ajaxurl, data, function (response) {
        console.log(response);
        if (response == "success") {
          alert("Order sync success");
          location.reload();
        } else {
          alert(response);
          //location.reload();
        }
        thiss.text("Sync").prop("disabled", false);
      });
    }
  });

  jQuery(document).ready(function () {
    /**
     * JS functionality for fetching prices
     */

    var ajaxurl = sitesettings.ajaxurl;
    var wcShipitFetchPrices = {
      init: function () {
        this.openModal();
      },

      openModal: function () {
        var self = this;
        jQuery("a#shipit-uniwin-fetch-prices").click(function (e) {
          e.preventDefault();
          jQuery("#wc-backbone-modal-dialog-carrier-agent").hide();
          jQuery("#wc-backbone-modal-dialog").show();
          self.fetchPrices();
        });
      },

      fetchPrices: function () {
        var self = this;

        var order_id = jQuery(".nordic-shipping-post-id").val();

        var nonce = jQuery('input[name="mv_other_meta_field_nonce"]').val();

        var data = {
          action: "NODS_shipit_services_price",
          order_id: order_id,
          weight: jQuery('input[name="uniwin_shipit_weight"]').val(),
          height: jQuery('input[name="uniwin_shipit_height"]').val(),
          length: jQuery('input[name="uniwin_shipit_length"]').val(),
          width: jQuery('input[name="uniwin_shipit_width"]').val(),
          parcels: jQuery('input[name="uniwin_shipit_parcel"]').val(),
          nonce: nonce,
          fragile: jQuery('input[name="uniwin_shipit_fragile"]').is(":checked"),
        };

        jQuery.ajax({
          url: ajaxurl,
          data: data,
          method: "POST",
          success: function (response) {
            jQuery("#shipit-uniwin-prices").html(response);
          },
          error: function (response) {
            alert("Unknown error");
          },
        });
      },
    };
    wcShipitFetchPrices.init();

    var wcShipitFetchAgent = {
      init: function () {
        this.openModal();
        jQuery("#wc-backbone-modal-dialog").hide();
      },

      openModal: function () {
        var self = this;
        jQuery("a#shipit_uniwin_agents").click(function (e) {
          e.preventDefault();
          jQuery("#wc-backbone-modal-dialog-carrier-agent").show();
          self.fetchAgent();
        });
      },

      fetchAgent: function () {
        var self = this;
        var nonce = jQuery('input[name="mv_other_meta_field_nonce"]').val();
        var order_id = jQuery(".nordic-shipping-post-id").val();
        var data = {
          action: "NODS_shipit_agent_data",
          order_id: order_id,
          nonce: nonce,
        };
        jQuery.ajax({
          url: ajaxurl,
          data: data,
          method: "POST",
          success: function (response) {
            jQuery("#shipit-uniwin-carrieragent").html(response);
          },
          error: function (response) {
            alert("Unknown error");
          },
        });
      },
    };

    wcShipitFetchAgent.init();
  });
  jQuery(document).on("click", "#suca_agent_search", function (e) {
    e.preventDefault();
    var ajaxurl = sitesettings.ajaxurl;
    var order_id = jQuery(".nordic-shipping-post-id").val();
    if (jQuery('input[name="suca_postal_code"]').val() == "") {
      jQuery('input[name="suca_postal_code"]').focus();
    }
    jQuery("#suca_fetched_agent_data select").prop("disabled", true);
    jQuery("#suca_fetched_agent_data input[type='button']").prop(
      "disabled",
      true
    );
    var nonce = jQuery('input[name="mv_other_meta_field_nonce"]').val();
    var data = {
      action: "NODS_shipit_agent_data",
      order_id: order_id,
      service_id: jQuery('select[name="suca_shipping_method"]').val(),
      postal_code: jQuery('input[name="suca_postal_code"]').val(),
      country: jQuery('input[name="suca_country"]').val(),
      instance_id: jQuery('input[name="suca_instance_id"]').val(),
      nonce: nonce,
    };
    jQuery.ajax({
      url: ajaxurl,
      data: data,
      method: "POST",
      success: function (response) {
        jQuery("#shipit-uniwin-carrieragent").html(response);
        jQuery("#suca_fetched_agent_data select").prop("disabled", false);
        jQuery("#suca_fetched_agent_data input[type='button']").prop(
          "disabled",
          false
        );
      },
      error: function (response) {
        alert("Unknown error");
      },
    });
  });

  jQuery(document).on("change", "#suca_shipping_method", function (e) {
    e.preventDefault();
    jQuery("#suca_fetched_agent_data select").prop("disabled", true);
    jQuery("#suca_fetched_agent_data input[type='button']").prop(
      "disabled",
      true
    );
  });
  jQuery(document).on("click", "#suca_save", function (e) {
    e.preventDefault();
    var nonce = jQuery('input[name="mv_other_meta_field_nonce"]').val();

    var ajaxurl = sitesettings.ajaxurl;
    var order_id = jQuery(".nordic-shipping-post-id").val();
    var instance_id = jQuery('input[name="suca_instance_id"]').val();
    var agent_id = jQuery('select[name="suca_agent_id"]').val();
    var agent_data_id =
      "carrier_agents:" + instance_id + ":" + agent_id + ":id";
    var agent_data_name =
      "carrier_agents:" + instance_id + ":" + agent_id + ":name";
    var agent_data_address1 =
      "carrier_agents:" + instance_id + ":" + agent_id + ":address1";
    var agent_data_zipcode =
      "carrier_agents:" + instance_id + ":" + agent_id + ":zipcode";
    var agent_data_city =
      "carrier_agents:" + instance_id + ":" + agent_id + ":city";
    var agent_data_countryCode =
      "carrier_agents:" + instance_id + ":" + agent_id + ":countryCode";
    var agent_data_serviceId =
      "carrier_agents:" + instance_id + ":" + agent_id + ":serviceId";
    var agent_data_carrier =
      "carrier_agents:" + instance_id + ":" + agent_id + ":carrier";
    var agent_data_carrierLogo =
      "carrier_agents:" + instance_id + ":" + agent_id + ":carrierLogo";

    var data = {
      action: "NODS_save_shipit_agent_data",
      nonce: nonce,
      order_id: order_id,
      agent_id: agent_id,
      agent_data_id: jQuery('input[name="' + agent_data_id + '"]').val(),
      agent_data_name: jQuery('input[name="' + agent_data_name + '"]').val(),
      agent_data_address1: jQuery(
        'input[name="' + agent_data_address1 + '"]'
      ).val(),
      agent_data_zipcode: jQuery(
        'input[name="' + agent_data_zipcode + '"]'
      ).val(),
      agent_data_city: jQuery('input[name="' + agent_data_city + '"]').val(),
      agent_data_countryCode: jQuery(
        'input[name="' + agent_data_countryCode + '"]'
      ).val(),
      agent_data_serviceId: jQuery(
        'input[name="' + agent_data_serviceId + '"]'
      ).val(),
      agent_data_carrier: jQuery(
        'input[name="' + agent_data_carrier + '"]'
      ).val(),
      agent_data_carrierLogo: jQuery(
        'input[name="' + agent_data_carrierLogo + '"]'
      ).val(),
    };

    jQuery.ajax({
      url: ajaxurl,
      data: data,
      method: "POST",
      success: function (response) {
        alert(response.html);
        location.reload();
      },
      error: function (response) {
        alert(response.html);
        location.reload();
      },
    });
  });
  $(document).on("click", "#uniwin_shipit_save", function (e) {
    var is_shipit_info = jQuery("#is_shipit_info").val();
    if (is_shipit_info == 1) {
      if (
        confirm(
          "You already created the shipment for this order, need to create again?"
        )
      ) {
        return true;
      } else {
        return false;
      }
    }
  });
})(jQuery);

jQuery(document).ready(function () {
  // Initially hide all tab contents except the first one
  jQuery(".tab-content").hide();
  jQuery("#content1").show();

  // Handle tab clicks
  jQuery(".tab").click(function () {
    // Remove active class from all tabs and tab contents
    jQuery(".tab").removeClass("active");
    jQuery(".tab-content").removeClass("active");

    // Add active class to the clicked tab and corresponding tab content
    jQuery(this).addClass("active");
    jQuery("#" + jQuery(this).attr("id").replace("tab", "content")).addClass(
      "active"
    );
  });
});

jQuery(document).ready(function ($) {
  function hideFIField() {
    // Replace '#field-id' with the actual selector of the field you want to hide
    $("#woocommerce_Shipit_shipping_service_ids").hide();
    $('label[for="woocommerce_Shipit_shipping_service_ids"]').hide();
  }

  function hideSEField() {
    $("#woocommerce_Shipit_shipping_se_service_ids").hide();
    $('label[for="woocommerce_Shipit_shipping_se_service_ids"]').hide();
  }

  function showFIField() {
    // Replace '#field-id' with the actual selector of the field you want to hide
    $("#woocommerce_Shipit_shipping_service_ids").show();
    $('label[for="woocommerce_Shipit_shipping_service_ids"]').show();
  }
  function showSEField() {
    // Replace '#field-id' with the actual selector of the field you want to hide
    $("#woocommerce_Shipit_shipping_se_service_ids").show();
    $('label[for="woocommerce_Shipit_shipping_se_service_ids"]').show();
  }
  // Listen for the click event on the "Add Shipping Method" button
  $(document).on("click",".wc-backbone-modal-add-shipping-method .inner #btn-next",function (e) {
      e.preventDefault();
      setTimeout(function () {
        console.log($('select[name="woocommerce_Shipit_shipping_country_region"]').val());
        if ($('select[name="woocommerce_Shipit_shipping_country_region"]').val() == "FI") {
          showFIField();
          hideSEField();
        } else if ($('select[name="woocommerce_Shipit_shipping_country_region"]').val() == "SE") {
          showSEField();
          hideFIField();
        }
      }, 900);
    }
  );

  // Listen for the click event on the "Edit" link for existing shipping methods
  $(document).on("click", ".wc-shipping-zone-method-settings", function () {
    console.log("Shipping method settings popup opened2");
    hideFIField();
    hideSEField();
    setTimeout(function () {
      if ($("#woocommerce_Shipit_shipping_country_region").val() == "FI") {
        showFIField();
      } else if (
        $("#woocommerce_Shipit_shipping_country_region").val() == "SE"
      ) {
        showSEField();
      }
    }, 100); // Adjust the timeout as necessary
  });
  $(document).on("change", ".nods_shipit_country_in_woos", function (e) {
    e.preventDefault();
    var selectedValue = $(this).val();
    if (selectedValue == "FI") {
      showFIField();
      hideSEField();
    } else if (selectedValue == "SE") {
      showSEField();
      hideFIField();
    } else {
      hideFIField();
      hideSEField();
    }
  });
  $(document).on("change", "#metabox_nods_uniwin_shipit_country", function (e) {
    e.preventDefault();
    var data = {
      action: "NODS_get_carrier_agents_based_on_country",
      country_id: $(this).val(),
    };
    var thiss = $(this);
    jQuery.post(ajaxurl, data, function (response) {
      if (response != "") {
        $("#nods_uniwin_shipit_service_woo_odetail").empty().append(response);
      }
    });
  });
});
