(function ($) {
    "use strict";

    $(document).on('change', '.better_payment_preferences', function (e) {
        var $this = $(this),
            preferences = $this.val();

        var elements = $(".better-payment-elements-container .better-payment-elements-info input[type=checkbox]");
        if (elements.length > 0) {
            if (preferences == 'custom') {
                elements.prop('checked', true)
            } else {
                elements.prop('checked', false)
                elements.each(function (i, item) {
                    if (preferences == 'advance' && $(item).data('preferences') != '') {
                        $(item).prop('checked', true)
                    } else if ($(item).data('preferences') == preferences) {
                        $(item).prop('checked', true)
                    }
                })
            }
        }
    });

    betterPaymentRenderTab();

    function betterPaymentRenderTab(step = 0) {
        var contents = document.getElementsByClassName("setup-content"),
            prev = document.getElementById("better-payment-prev"),
            nextElement = document.getElementById("better-payment-next"),
            saveElement = document.getElementById("better-payment-save");
        
        if (contents.length < 1) {
            return;
        }

        contents[step].style.display = "block";
        prev.style.display = (step == 0) ? "none" : "inline";

        if (step == (contents.length - 1)) {
            saveElement.style.display = "inline";
            nextElement.style.display = "none";
        } else {
            nextElement.style.display = "inline";
            saveElement.style.display = "none";
        }
        betterPaymentStepIndicator(step)
    }

    function betterPaymentStepIndicator(stepNumber) {
        var steps = document.getElementsByClassName("step"),
            container = document.getElementsByClassName("better-payment-setup-wizard");
        container[0].setAttribute('data-step', stepNumber);

        for (var i = 0; i < steps.length; i++) {
            steps[i].className = steps[i].className.replace(" active", "");
        }

        steps[stepNumber].className += " active";
    }

    $(document).on('click', '#better-payment-next,#better-payment-prev', function (e) {
        var container = document.getElementsByClassName("better-payment-setup-wizard"),
            StepNumber = parseInt(container[0].getAttribute('data-step')),
            contents = document.getElementsByClassName("setup-content");

        contents[StepNumber].style.display = "none";
        StepNumber = (e.target.id == 'better-payment-prev') ? StepNumber - 1 : StepNumber + 1;

        if (StepNumber >= contents.length) {
            return false;
        }
        betterPaymentRenderTab(StepNumber);
    });

    $('.btn-collect').on('click', function () {
        $(".better-payment-whatwecollecttext").toggle();
    });

    //Stripe toggle button
    $(document).on('change', '.quick-setup-stripe input[name="better_payment_settings_payment_stripe_live_mode"]', function (e) {
        e.preventDefault();
        let bpAdminSettingsPaymentStripe = $(this).attr('data-targettest');

        if ($(this).is(':checked')) {
            bpAdminSettingsPaymentStripe = $(this).attr('data-targetlive');
        }

        $('.bp-stripe-key').removeClass('bp-d-block').addClass('bp-d-none');
        $(`.${bpAdminSettingsPaymentStripe}`).removeClass('bp-d-none').addClass('bp-d-block');
    });

    //Settings Save
    $(document).on("click", ".better-payment-setup-wizard-save", function (e) {
        e.preventDefault();
        let bpAdminSettingsForm = $(this).parents("#better-payment-admin-settings-form");
        bpAdminSettingsSave(this, bpAdminSettingsForm);
    });

    function bpAdminSettingsSave(button, form) {
        let bpAdminSettingsSaveBtn = $(button),
            nonce = betterPaymentObjWizard.nonce;
            
        let formDataWizard = $('#better-payment-admin-settings-form').serializeArray();
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "save_setup_wizard_data",
                nonce: nonce,
                form_data: formDataWizard,
            },
            beforeSend: function () {
                bpAdminSettingsSaveBtn.addClass("is-loading");
            },
            success: function (response) {
                bpAdminSettingsSaveBtn.removeClass("is-loading");
                
                if (response.success) {
                    Swal.fire({
                        timer: 3000,
                        showConfirmButton: false,
                        imageUrl: betterPaymentObjWizard.success_image,
                    }).then((result) => {
                        window.location = response.data.redirect_url;
                    });
                } else {
                    Swal.fire({
                        type: "error",
                        title: 'Error',
                        text: 'error',
                    });
                }
            },
        });
    }

})(jQuery);