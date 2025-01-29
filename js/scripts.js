jQuery(document).ready(function () {


  jQuery("input[type='email']").on("change", function () {
    let email = jQuery("input[type='email']").val();
    let inputId = jQuery("input[type='email']").attr('id');
    let fieldId = inputId.replace('input', 'field');
    let gravityFormsDetected = jQuery('.gform_anchor').length;
    let ninjaFormsDetected = jQuery('.nf-form-cont').length;

    jQuery.ajax({
      type: "post",
      url: `${window.location.origin}/wp-admin/admin-ajax.php`,
      data: {
        action: "bp_kickbox_validate_email",
        data: {
          email: email
        }, // any JS object
      },
      complete: function (response) {
        let result = JSON.parse(response.responseText);
        let resultStatus = result.status;

        if (gravityFormsDetected) {
          bpKickboxGravityForms(fieldId, resultStatus);
        }

        // if (ninjaFormsDetected) {
        //   bpKickboxNinjaForms(fieldId, resultStatus);
        // }

      },
    });

  });
});

function bpKickboxGravityForms(fieldId, resultStatus) {
  var errorMessage = "Invalid email, please enter a valid email.";

  jQuery("#" + fieldId + ' .validation_message').remove();
  jQuery("#" + fieldId).removeClass('gform_validation_error');
  jQuery("#" + fieldId).removeClass('gfield_error');

  if (resultStatus == 'fail') {
    jQuery("#" + fieldId).addClass("gfield_error");
    jQuery("#" + fieldId).closest(".gfield").append("<div class='gfield_description validation_message'>" + errorMessage + "</div>");
  }
}

function bpKickboxNinjaForms(fieldId, resultStatus) {
  var errorMessage = "Invalid email, please enter a valid email.";

  jQuery("#" + fieldId + ' .validation_message').remove();
  jQuery("#" + fieldId).removeClass('gform_validation_error');
  jQuery("#" + fieldId).removeClass('gfield_error');

  if (resultStatus == 'fail') {
    jQuery("#" + fieldId).addClass("gfield_error");
    jQuery("#" + fieldId).closest(".gfield").append("<div class='gfield_description validation_message'>" + errorMessage + "</div>");
  }
}