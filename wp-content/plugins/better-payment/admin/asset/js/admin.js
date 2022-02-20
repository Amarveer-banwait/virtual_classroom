; (function ($) {
    $(document).ready(function () {
        'use strict'

        //Settings Page Starts
        $('.bp-tabs .tab__link').on('click', function (e) {
            e.preventDefault();
            $('.bp-tabs .tab__link').removeClass('active');
            $(this).addClass('active');

            var dataId = $(this).data('id');

            $('.bp-tabs .tab__content__item').removeClass('show').fadeOut();
            $('#' + dataId).addClass('show').fadeIn();

        });

        $(document).on('click', '.sidebar__menu a', function (e) {
            e.preventDefault();

            let sidebar_link_class = 'sidebar__link';
            if ($(this).hasClass('sidebar__link_submenu')) {
                sidebar_link_class = 'sidebar__link_submenu';
            }

            $('.sidebar__menu .' + sidebar_link_class).removeClass('active');
            $(this).addClass('active');

            var dataId = $(this).data('id');
            $('.content__area__body .sidebar__tab__content').removeClass('show').fadeOut();
            $('#' + dataId).addClass('show').fadeIn();

            //Auto active first submenu item
            if ($(this).hasClass('sidebar__link') && (dataId === 'admin-email' || dataId === 'paypal')) {
                $(this).siblings('ul').find('li a').removeClass('active')
                $(this).siblings('ul').find('li:first-child a').addClass('active');
            }
        });

        $(document).on('click', ".sidebar__item .sidebar__link, .email-additional-headers", function (e) {
            e.preventDefault();
            let $this = $(this);
            let bpTargetSelector = $(this).hasClass('email-additional-headers') ? '.email-additional-headers-content' : '.sub__menu';

            if ($this.siblings(bpTargetSelector).hasClass('show')) {
                $this.siblings(bpTargetSelector).removeClass('show').slideUp();
            } else {
                $(bpTargetSelector + '.show').slideUp().removeClass('show');
                $this.siblings(bpTargetSelector).addClass('show').slideDown();
            }
        });

        //Stripe toggle button
        $(document).on('change', '.better-payment-settings-payment-stripe input[name="better_payment_settings_payment_stripe_live_mode"]', function (e) {
            e.preventDefault();
            let bpAdminSettingsPaymentStripe = $(this).attr('data-targettest');

            if ($(this).is(':checked')) {
                bpAdminSettingsPaymentStripe = $(this).attr('data-targetlive');
            }

            $('.bp-stripe-key').removeClass('bp-d-block').addClass('bp-d-none');
            $(`.${bpAdminSettingsPaymentStripe}`).removeClass('bp-d-none').addClass('bp-d-block');
        });

        //PayPal toggle button
        $(document).on('change', '.better-payment-settings-payment-paypal input[name="better_payment_settings_payment_paypal_live_mode"]', function (e) {
            e.preventDefault();
            let bpAdminSettingsPaymentPayPal = $(this).attr('data-targettest');

            if ($(this).is(':checked')) {
                bpAdminSettingsPaymentPayPal = $(this).attr('data-targetlive');
            }

            $('.bp-paypal-key').removeClass('bp-d-block').addClass('bp-d-none');
            $(`.${bpAdminSettingsPaymentPayPal}`).removeClass('bp-d-none').addClass('bp-d-block');
        });

        //Settings Save
        $(document).on("click", ".better-payment-admin-settings-button", function (e) {
            e.preventDefault();
            let bpAdminSettingsForm = $(this).parents("#better-payment-admin-settings-form");
            bpAdminSettingsSave(this, bpAdminSettingsForm);
        });

        function bpAdminSettingsSave(button, form) {
            let bpAdminSettingsSaveBtn = $(button),
                nonce = betterPaymentObj.nonce,
                formData = $(form).serializeArray();

            let bpIsFormValidated = bpValidateFormFields(formData);

            //Reset button
            $('.better-payment-settings-reset').val(0);
            if ($(button).hasClass('better-payment-reset-button')) {
                $('.better-payment-settings-reset').val(1);
            }

            if (!bpIsFormValidated) {
                return false;
            }

            let bpIsResetButton = $(button).hasClass('better-payment-reset-button');

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "better_payment_settings_action",
                    nonce: nonce,
                    form_data: formData,
                    reset_button: bpIsResetButton,
                },
                beforeSend: function () {
                    bpAdminSettingsSaveBtn.addClass("is-loading");
                },
                success: function (res) {
                    bpAdminSettingsSaveBtn.removeClass("is-loading");
                    if (res.data === "success") {
                        toastr.success('Changes saved successfully!');
                    } else {
                        toastr.error('Opps! something went wrong!');
                    }
                },
            });
        }

        function bpValidateFormFields(formFields, emailFields = []) {
            $(`#better-payment-admin-settings-form input`).removeClass('is-danger');
            if (!(emailFields.length)) {
                emailFields = [
                    'better_payment_settings_general_email_to',
                    'better_payment_settings_general_email_from_email',
                    'better_payment_settings_general_email_reply_to',
                    'better_payment_settings_general_email_cc',
                    'better_payment_settings_general_email_bcc',
                    'better_payment_settings_general_email_to_customer',
                    'better_payment_settings_general_email_from_email_customer',
                    'better_payment_settings_general_email_reply_to_customer',
                    'better_payment_settings_general_email_cc_customer',
                    'better_payment_settings_general_email_bcc_customer',
                    'better_payment_settings_payment_paypal_email'
                ];
            }

            let formFieldName = '',
                formFieldValue = '',
                formField = '',
                isFormFieldValidated = false,
                errorMessageStr = ''
                ;
            for (const property in formFields) {
                formField = formFields[property];
                formFieldName = formField.name;
                formFieldValue = formField.value;

                if ((emailFields.indexOf(formFieldName) >= 0) && formFieldValue != '') {

                    isFormFieldValidated = admin_better_email_validation(formFieldValue);

                    if (!isFormFieldValidated) {
                        if (formFieldName == 'better_payment_settings_general_email_to') {
                            errorMessageStr = 'Admin Email: To Email';
                        } else if (formFieldName == 'better_payment_settings_general_email_from_email') {
                            errorMessageStr = 'Admin Email: From Email';
                        } else if (formFieldName == 'better_payment_settings_general_email_reply_to') {
                            errorMessageStr = 'Admin Email: Reply-To';
                        } else if (formFieldName == 'better_payment_settings_general_email_cc') {
                            errorMessageStr = 'Admin Email: Cc';
                        } else if (formFieldName == 'better_payment_settings_general_email_bcc') {
                            errorMessageStr = 'Admin Email: Bcc';
                        } if (formFieldName == 'better_payment_settings_general_email_to_customer') {
                            errorMessageStr = 'Customer Email: To Email';
                        } else if (formFieldName == 'better_payment_settings_general_email_from_email_customer') {
                            errorMessageStr = 'Customer Email: From Email';
                        } else if (formFieldName == 'better_payment_settings_general_email_reply_to_customer') {
                            errorMessageStr = 'Customer Email: Reply-To';
                        } else if (formFieldName == 'better_payment_settings_general_email_cc_customer') {
                            errorMessageStr = 'Customer Email: Cc';
                        } else if (formFieldName == 'better_payment_settings_general_email_bcc_customer') {
                            errorMessageStr = 'Customer Email: Bcc';
                        } else if (formFieldName == 'better_payment_settings_payment_paypal_email') {
                            errorMessageStr = 'PayPal Business Email';
                        }

                        toastr.error(`Invalid email address on ${errorMessageStr} field!`);
                        $(`#better-payment-admin-settings-form input[name="${formFieldName}"]`).addClass('is-danger');
                        return false;
                    }
                }
            }

            return true;
        }

        function admin_better_email_validation(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        }
        //Settings Page Ends

        //Transaction Page Starts
        $(document).on("change", ".better-payment .header__show_entries select", function (e) {
            e.preventDefault();
            let perPageVal = $(this).val();
            location = location.origin + location.pathname + location.search + '&paged=1&per_page=' + perPageVal + location.hash;
        });

        $(document).on("click", ".better-payment-transaction-edit, .better-payment-transaction-email-resend", function (e) {
            e.preventDefault();
            toastr.warning('Coming REAL Soon!');
        });
        //Pagination

        //Filter transactions
        $(document).on("click", ".better-payment-transaction-filter", function (e) {
            e.preventDefault();
            let paymentFromDate = $('.better-payment .payment_date_from').val();
            let paymentToDate = $('.better-payment .payment_date_to').val();
            if(paymentFromDate != '' && paymentToDate != ''){
                if(paymentFromDate > paymentToDate){
                    toastr.error('From Date must be smaller than To Date!');
                    return false;
                }
            }
            let tableSelector = $('.better-payment-admin-transactions-page .transaction__table');

            let filterFormData = {};
            filterFormData.search_text = $('.better-payment .serch-text').val();
            
            filterFormData.payment_date_from = $('.better-payment .payment_date_from').val();
            filterFormData.payment_date_to = $('.better-payment .payment_date_to').val();
            filterFormData.order_by = $('.better-payment .order-by').val();
            filterFormData.order = $('.better-payment .order').val();
            filterFormData.source = $('.better-payment .source').val();
            
            filterFormData.paged = $('.better-payment .paged').val();
            filterFormData.per_page = $('.better-payment .per-page').val();
            filterFormData.total_entry = $('.better-payment .total-entry').val();
            
            bpTransactionFilter(tableSelector, filterFormData);
        });
        
        let elements = document.getElementsByClassName("bp-copy-clipboard");
        Array.from(elements).forEach(function(element) {
            element.addEventListener('click', bpTransactionIdCopy);
        });

        function bpTransactionIdCopy() {
            let bpTxnCounter = $(this).attr('data-bp_txn_counter');
            let node = 'bp_copy_clipboard_input_'+bpTxnCounter;

            node = document.getElementById(node);

            if (document.body.createTextRange) {
                const range = document.body.createTextRange();
                range.moveToElementText(node);
                range.select();

                document.execCommand("copy");

            } else if (window.getSelection) {

                const selection = window.getSelection();
                const range = document.createRange();
                range.selectNodeContents(node);
                selection.removeAllRanges();
                selection.addRange(range);

                document.execCommand("copy");
                selection.removeAllRanges();

            } else {
                console.warn("Could not select text in node: Unsupported browser.");
            }
            $('.bp-copy-clipboard').attr('title', 'Copy').css('color', '#2a3256');
            $(this).attr('title', 'Copied!').css('color', '#6e58f7');
        }
          
        function bpTransactionFilter(tableSelector, filterFormData){
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "better-payment-filter-transaction",
                    nonce: betterPaymentObj.nonce,
                    filterFormData: filterFormData,
                },
                dataType: "html",
                beforeSend: function () {
                    tableSelector.css('opacity', '.5');
                },
                success: function (resultHtml) {
                    $('.better-payment .transaction-table-wrapper').replaceWith(resultHtml);
                    tableSelector.css('opacity', '1');
                },
            });
        }
        //Transaction Page Ends

        //Helper Functions Starts
        function toasterOptions() {
            toastr.options = {
                "timeOut": "2000",
                "toastClass": "font-size-md",
                "positionClass": 'toast-top-center',
                "showMethod": "slideDown",
                "hideMethod": "slideUp",

            };
        };
        toasterOptions();

        $(".bp-datepicker").datepicker({
            dateFormat : "dd-mm-yy"
        });
        //Helper Functions Ends

    });
})(jQuery);