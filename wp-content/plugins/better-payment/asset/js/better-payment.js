(function ($) {
    "use strict";

    $(".better-payment-stripe-bt").on('click', function (e) {
        e.preventDefault();
        const id = $("#" + this.form.id);
        const $form = $(id);
        const $this = $(this);
        const setting_data = $form.data('better-payment');
        const fields = $form.serializeObject();

        let betterFormValidated = betterFormValidation(this.form, fields);
        
        if(!betterFormValidated){
            return false;
        }

        if (typeof fields.pay_amount == 'undefined' || fields.pay_amount == '') {
            toastr.error('Amount field is required!');
            return false;
        }

        if (parseFloat(fields.pay_amount) < 1) {
            toastr.error('Minimum amount is 1!');
            return false;
        }

        if (typeof fields.email != 'undefined' && fields.email !='' && !better_email_validation(fields.email)) {
            toastr.error('Email address is invalid!');
            return false;
        }

        $(this).html('Submitting <span class="elementor-control-spinner">&nbsp;<i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>')

        $.ajax({
            url: betterPayment.ajax_url,
            type: "post",
            data: {
                action: "better_payment_stripe_get_token",
                security: betterPayment.nonce,
                fields: fields,
                setting_data: setting_data,
            },

            success: function (response) {
                if (typeof response.data.stripe_data != 'undefined') {
                    $this.html('Redirecting <span class="elementor-control-spinner">&nbsp;<i class="eicon-spinner eicon-animation-spin"></i>&nbsp;</span>')
                    var stripe = Stripe(response.data.stripe_public_key);
                    stripe.redirectToCheckout({sessionId: response.data.stripe_data}).then(function (t) {
                    })
                } else {
                    $this.html('Stripe');
                    toastr.error(response.data);
                }
            },
            error: function () {
                console.log('Error');
            },
        });
    })

    function better_email_validation (email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
    }

    $(".better-payment-paypal-bt").on('click', function (e) {
        const id = $("#" + this.form.id),
            $form = $(id),
            payment_info = $(this).data("paypal-info"),
            fields = $form.serializeObject();
        let status = true;

        let betterFormValidated = betterFormValidation(this.form, fields);
        if(!betterFormValidated){
            return false;
        }
        
        if (typeof fields.pay_amount == 'undefined' || fields.pay_amount == '') {
            status = false;
            toastr.error('Amount field is required!');
        }

        if (!payment_info.business_email) {
            status = false;
            toastr.error('Business Email is required!');
        }

        if (!status) {
            e.preventDefault();
            return false;
        }

    });

    $.fn.serializeObject = function () {
        var objInit = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (objInit[this.name]) {
                if (!objInit[this.name].push) {
                    objInit[this.name] = [objInit[this.name]];
                }
                objInit[this.name].push(this.value || '');
            } else {
                objInit[this.name] = this.value || '';
            }
        });
        return objInit;
    };

    $('.bp-custom-pay-amount').on('change', function (e) {
        if (typeof this.form.id == 'undefined') {
            toastr.error('Something went wrong!');
        }

        const radioInput = document.querySelectorAll("#" + this.form.id + ' .bp-payment-amount-wrap input[type="radio"]');
        radioInput.forEach((radio) => {
            radio.checked = false
        });

    });

    $(".bp-form_pay-radio").on('click', function (e) {
        if (typeof this.form.id == 'undefined') {
            toastr.error('Something went wrong!');
        }
        const $this = $(this)
        var amount = parseFloat($this.val());

        if (amount == '') {
            return false;
        }
        $("#" + this.form.id + ' .bp-custom-pay-amount').val(amount);
    });

    $(window).on('elementor/frontend/init', () => {

        const Better_Payment_Stripe = elementorModules.frontend.handlers.Base.extend({
            getDefaultSettings: function getDefaultSettings() {
                return {
                    selectors: {
                        form: '.elementor-form'
                    }
                };
            },

            getDefaultElements: function getDefaultElements() {
                let selectors = this.getSettings('selectors'),
                    elements = {};
                elements.$form = this.$element.find(selectors.form);
                return elements;
            },

            bindEvents: function bindEvents() {
                this.elements.$form.on('submit_success', this.handleFormAction);
            },

            handleFormAction: function handleFormAction(event, res) {
                if (typeof res.data.better_stripe_data != 'undefined') {
                    let stripe = Stripe(res.data.better_stripe_data.stripe_public_key);
                    stripe.redirectToCheckout({sessionId: res.data.better_stripe_data.stripe_data}).then(function (t) {
                    });
                }
            },
        });

        const Better_Payment_Handler = ($element) => {
            elementorFrontend.elementsHandler.addHandler(Better_Payment_Stripe, {
                $element,
            });
        };
        elementorFrontend.hooks.addAction('frontend/element_ready/form.default', Better_Payment_Handler);
        
        if(typeof elementor != 'undefined'){
           
            elementor.hooks.addAction( 'panel/open_editor/widget/form', function( panel, model, view ) {

                elementor.hooks.addFilter('elementor_pro/forms/content_template/item', function(item, i, settings){
                    let bp_field_pay_amount = 'pay_amount';
                    let better_payment_pay_amount_enable_settings = '';

                    if(typeof settings.better_payment_pay_amount_enable !== 'undefined'){
                        better_payment_pay_amount_enable_settings = settings.better_payment_pay_amount_enable;
                    }
                    console,l
                    if(typeof item.field_type !== 'undefined' && item.field_type == bp_field_pay_amount && better_payment_pay_amount_enable_settings === 'yes' ){
                        let bp_field_label = item.field_label;
                        let bp_field_placeholder = typeof item.bp_placeholder !== 'undefined' ? item.bp_placeholder : '';
                        let better_payment_form_currency = 'USD';
                        
                        if(settings.submit_actions.includes('stripe')){
                            better_payment_form_currency = (typeof settings.better_payment_form_stripe_currency !== 'undefined') ? settings.better_payment_form_stripe_currency : better_payment_form_currency;
                        }else if(settings.submit_actions.includes('paypal')){
                            better_payment_form_currency = (typeof settings.better_payment_form_paypal_currency !== 'undefined') ? settings.better_payment_form_paypal_currency : better_payment_form_currency;
                        }

                        let better_payment_form_currency_symbol = get_currency_symbols(better_payment_form_currency);

                        let bp_pay_amount_label = `<label for="form-field-${bp_field_pay_amount}" class="elementor-field-label">${bp_field_label}</label>`;
                        let bp_pay_amount_input = `<input type="number" name="form_fields[${bp_field_pay_amount}]" id="form-field-${bp_field_pay_amount}" class="elementor-field elementor-size-sm elementor-field-textual bp-elementor-field-textual-amount" min="${item.bp_field_min}" max="${item.bp_field_max}" placeholder="${bp_field_placeholder}" >`;
            
                        let pay_amount_input_group = `  <div class="bp-input-group mb-2">
                                                            <div class="bp-input-group-prepend">
                                                                <div class="bp-input-group-text" title="${better_payment_form_currency}">${better_payment_form_currency_symbol}</div>
                                                            </div>
                                                            ${bp_pay_amount_input}
                                                        </div>
                                                    `;

                        item.field_type = 'html';
                        item.field_html = bp_pay_amount_label + pay_amount_input_group;
                    }

                    return item;
                }, 10, 3 );

            } ); 
        }
        
    });

    //Better Payment Functions Starts
    function betterFormValidation(formSelector, fields){
        let betterFormValidated = false;
        let firstNameValidated = true; 
        let lastNameValidated = true; 
        let emailValidated = true;

        if( typeof formSelector['first_name'] != 'undefined' ){
            firstNameValidated = betterFieldValidation(formSelector['first_name'], fields.first_name);
            if(!firstNameValidated){
                return false;
            }
        }
         
        if( typeof formSelector['last_name'] != 'undefined' ){
            lastNameValidated = betterFieldValidation(formSelector['last_name'], fields.last_name);
            if(!lastNameValidated){
                return false;
            }
        }
        
        if( typeof formSelector['email'] != 'undefined' ){
            emailValidated = betterFieldValidation(formSelector['email'], fields.email);
            if(!emailValidated){
                return false;
            }
        }
        
        if(firstNameValidated && lastNameValidated && emailValidated){
            betterFormValidated = true;
        }

        return betterFormValidated;
    } 

    function get_currency_symbols( currency = 'USD' ) {
        let better_payment_currency_symbol = '$';
        let better_payment_list = {
            'AUD': "$",
            'CAD': "$",
            'CZK': "Kč",
            'DKK': "kr",
            'EUR': "€",
            'HKD': "$",
            'HUF': "ft",
            'ILS': "₪",
            'JPY': "¥",
            'MXN': "$",
            'NOK': "kr",
            'NZD': "$",
            'PHP': "₱",
            'PLN': "zł",
            'GBP': "£",
            'RUB': "₽",
            'SGD': "$",
            'SEK': "kr",
            'CHF': "CHF",
            'TWD': "$",
            'THB': "฿",
            'TRY': "₺",
            'USD': "$",
        };

        if(currency in better_payment_list){
            better_payment_currency_symbol = better_payment_list[currency];
        }

        return better_payment_currency_symbol;
    }
    //Better Payment Functions Ends

    //Helper Functions Starts
    function betterFieldValidation(fieldSelector, fieldValue, alertMessage=''){
        let fieldRequired = isbetterFieldRequired(fieldSelector);

        if(fieldRequired){
            let itemPlaceholder = fieldSelector.placeholder;
            itemPlaceholder = itemPlaceholder.replace(' *','');

            let betterAlertMessage = alertMessage ? alertMessage : `${itemPlaceholder} field is required!`;
            if(typeof fieldValue == 'undefined' || fieldValue == ''){
                toastr.error(betterAlertMessage);
                return false;
            }
        }

        return true;
    } 

    function isbetterFieldRequired(fieldSelector){
        let betterFieldRequired = false;

        //if required attribute has value rather than null or ''
        betterFieldRequired = !(fieldSelector.getAttribute('required') == null || fieldSelector.getAttribute('required') == '');
        
        return betterFieldRequired;
    }

    function toasterOptions() {
        toastr.options = {
            "timeOut": "2000",
            toastClass: "font-size-md"
        };
    };
    toasterOptions();
    //Helper Functions Ends

})(jQuery);