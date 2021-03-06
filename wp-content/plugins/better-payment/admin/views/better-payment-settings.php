<?php
/*
 * Settings page
 *  All undefined vars comes from 'render_better_payment_admin_pages' method
 *  $bp_admin_saved_settings : contains all values
 */
?>
<?php
$bp_admin_settings_transactions_all_count = isset($bp_admin_all_transactions_analytics['all']) ? $bp_admin_all_transactions_analytics['all'] : 0;
$bp_admin_settings_transactions_paid_count = isset($bp_admin_all_transactions_analytics['paid']) ? $bp_admin_all_transactions_analytics['paid'] : 0;
$bp_admin_settings_transactions_processing_count = $bp_admin_settings_transactions_all_count - $bp_admin_settings_transactions_paid_count;
$currency_list = [
	'AUD' => 'AUD',
	'CAD' => 'CAD',
	'CZK' => 'CZK',
	'DKK' => 'DKK',
	'EUR' => 'EUR',
	'HKD' => 'HKD',
	'HUF' => 'HUF',
	'ILS' => 'ILS',
	'JPY' => 'JPY',
	'MXN' => 'MXN',
	'NOK' => 'NOK',
	'NZD' => 'NZD',
	'PHP' => 'PHP',
	'PLN' => 'PLN',
	'GBP' => 'GBP',
	'RUB' => 'RUB',
	'SGD' => 'SGD',
	'SEK' => 'SEK',
	'CHF' => 'CHF',
	'TWD' => 'TWD',
	'THB' => 'THB',
	'USD' => 'USD'
];
?>
<!-- Admin Settings Form Wrapper: Starts  -->
<div class="better-payment">
    <div class="template__wrapper background__grey">

        <form method="post" id="better-payment-admin-settings-form" action="#">
            <header class="pb30">
                <div class="bp-container">
                    <div class="bp-row">
                        <div class="bp-col-9">
                            <div class="logo">
                                <a href="javascript:void(0)"><img src="<?php echo esc_url(BETTER_PAYMENT_ADMIN_ASSET_URL . 'img/logo.svg'); ?>" alt=""></a>
                            </div>
                        </div>
                        <div class="bp-col-3">
                            <div class="control text-right">
                                <button type="submit" class="button button__active better-payment-admin-settings-button" data-nonce="<?php echo wp_create_nonce('better_payment_admin_settings_nonce'); ?>"><?php _e('Save Changes', 'better-payment'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="bp-container">
                <div class="bp-row">
                    <div class="bp-col-lg-9">
                        <div class="bp-tabs">
                            <ul class="tab__menu">
                                <li class="tab__list">
                                    <a href="#" class="tab__link active" data-id="settings"><i class="bp-icon bp-gear-alt"></i> <?php _e('Settings', 'better-payment'); ?></a>
                                </li>
                                <li class="tab__list">
                                   <a href="#go-premium" class="tab__link" data-id="premium"><i class="bp-icon bp-crown"></i><?php _e('Go Premium', 'better-payment'); ?></a>
                                </li>
                            </ul>
                            <div class="tab__content">
                                <div class="tab__content__item background__white show" id="settings">
                                    <div class="main__content__area">
                                        <div class="sidebar">
                                            <ul class="sidebar__menu">
                                                <li class="sidebar__item">
                                                    <a href="#" class="sidebar__link active" data-id="general"><i class="bp-icon bp-gear"></i> <?php _e('General', 'better-payment'); ?></a>
                                                </li>
                                                <li class="sidebar__item">
                                                    <a href="#" class="sidebar__link" data-id="admin-email"><i class="bp-icon bp-mail"></i> <?php _e('Email', 'better-payment'); ?></a>
                                                    <ul class="sub__menu">
                                                        <li><a href="#" class="sidebar__link_submenu" data-id="admin-email"><?php _e('Admin Email', 'better-payment'); ?></a></li>
                                                        <li><a href="#" class="sidebar__link_submenu" data-id="customer-email"><?php _e('Customer Email', 'better-payment'); ?></a></li>
                                                    </ul>
                                                </li>
                                                <li class="sidebar__item">
                                                    <a href="#" class="sidebar__link" data-id="paypal"><i class="bp-icon bp-card"></i> <?php _e('Payment', 'better-payment'); ?></a>
                                                    <ul class="sub__menu">
                                                        <li><a href="#" class="sidebar__link_submenu" data-id="paypal"><?php _e('PayPal', 'better-payment'); ?></a></li>
                                                        <li><a href="#" class="sidebar__link_submenu" data-id="stripe"><?php _e('Stripe', 'better-payment'); ?></a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="content__area__body">
                                            <div id="general" class="sidebar__tab__content show payment__options">
                                                <div class="payment__option">
                                                    <div class="payment__option__content">
                                                        <h4><?php _e('Stripe', 'better-payment'); ?></h4>
                                                        <p><?php _e('Enable Stripe if you want to accept payment via Stripe.', 'better-payment'); ?></p>
                                                    </div>
                                                    <div class="active__status">
                                                        <label class="bp-switch">
                                                            <input type="hidden" name="better_payment_settings_general_general_stripe" value="no">
                                                            <input type="checkbox" name="better_payment_settings_general_general_stripe" value="yes" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_general_stripe']) && $bp_admin_saved_settings['better_payment_settings_general_general_stripe'] == 'yes' ? ' checked' : '' ?>>
                                                            <span class="switch__btn"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="payment__option">
                                                    <div class="payment__option__content">
                                                        <h4><?php _e('PayPal', 'better-payment'); ?></h4>
                                                        <p><?php _e('Enable PayPal if you want to make transaction using PayPal.', 'better-payment'); ?></p>
                                                    </div>
                                                    <div class="active__status">
                                                        <label class="bp-switch">
                                                            <input type="hidden" name="better_payment_settings_general_general_paypal" value="no">
                                                            <input type="checkbox" name="better_payment_settings_general_general_paypal" value="yes" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_general_paypal']) && $bp_admin_saved_settings['better_payment_settings_general_general_paypal'] == 'yes' ? ' checked' : '' ?>>
                                                            <span class="switch__btn"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="payment__option">
                                                    <div class="payment__option__content">
                                                        <h4><?php _e('Email Notification', 'better-payment'); ?></h4>
                                                        <p><?php _e('Enable email notification for each transaction. It sends notification to the website admin and customer (who makes the payment). You can modify email settings as per your need.', 'better-payment'); ?></p>
                                                    </div>
                                                    <div class="active__status">
                                                        <label class="bp-switch">
                                                            <input type="hidden" name="better_payment_settings_general_general_email" value="no">
                                                            <input type="checkbox" name="better_payment_settings_general_general_email" value="yes" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_general_email']) && $bp_admin_saved_settings['better_payment_settings_general_general_email'] == 'yes' ? ' checked' : '' ?>>
                                                            <span class="switch__btn"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="payment__option">
                                                    <div class="payment__option__content">
                                                        <h4><?php _e('Currency', 'better-payment'); ?></h4>
                                                        <p><?php _e('Select default currency for each transaction. You can also overwrite this setting from each widget control on elementor page builder.', 'better-payment'); ?></p>
                                                    </div>
                                                    <div class="active__status">
                                                        <div class="bp-select">
                                                            <select name="better_payment_settings_general_general_currency">
                                                                <option value="" disabled> <?php _e('Select Currency', 'better-payment'); ?> </option>
                                                                <?php foreach ($currency_list as $key => $value){
                                                                    $selected = isset($bp_admin_saved_settings['better_payment_settings_general_general_currency']) && $bp_admin_saved_settings['better_payment_settings_general_general_currency'] == $key?'selected':'';
	                                                                printf( '<option value="%s" %s >%s</option>', $key, $selected, __( $key, 'better-payment' ) );
                                                                } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="admin-email" class="sidebar__tab__content p50">
                                                <div class="mailing__option mb30">
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('To', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" placeholder="<?php _e('Email address', 'better-payment'); ?>" name="better_payment_settings_general_email_to" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_to']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_to'] ) : '' ?>">
                                                            <p><?php _e('Enter website admin email address here. This email will be used to send email notification for each transaction.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Subject', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="text" class="form__control" placeholder="<?php _e('Email subject', 'better-payment'); ?>" name="better_payment_settings_general_email_subject" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_subject']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_subject'] ) : '' ?>">
                                                            <p><?php _e('Email subject for the admin email notification.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Message', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <textarea class="form__control" name="better_payment_settings_general_email_message_admin"><?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_message_admin']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_message_admin'] ) : '' ?> </textarea>
                                                            <p><?php _e('Email body for the admin email notification. ', 'better-payment'); ?> <code>[bp-all-fields]</code> <?php _e(' shortcode can be used to display all the form fields (which was used to make payments).', 'better-payment') ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="#" class="button add__button email-additional-headers"><span><i class="bp-icon bp-plus"></i></span> <?php _e('Additional Headers', 'better-payment'); ?></a>
                                                <div class="mailing__option mt30 email-additional-headers-content bp-d-none">
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('From Name', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="text" class="form__control" name="better_payment_settings_general_email_from_name" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_from_name']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_from_name'] ) : '' ?>">
                                                            <p><?php _e('From name that will be used in the email headers.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('From Email', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_from_email" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_from_email']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_from_email'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as From Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Reply-To', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_reply_to" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_reply_to']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_reply_to'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Reply-To Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Cc', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_cc" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_cc']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_cc'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Cc Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Bcc', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_bcc" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_bcc']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_bcc'] ) : '' ?>" type="email" placeholder="<?php _e('Bcc email', 'better-payment'); ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Bcc Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Send As', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <div class="bp-select">
                                                                <select name="better_payment_settings_general_email_send_as">
                                                                    <option value="" disabled> <?php _e('Select One', 'better-payment'); ?> </option>
                                                                    <option value="plain" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_send_as']) && $bp_admin_saved_settings['better_payment_settings_general_email_send_as'] == 'plain' ? ' selected' : '' ?>> <?php _e('Plain', 'better-payment'); ?> </option>
                                                                    <option value="html" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_send_as']) && $bp_admin_saved_settings['better_payment_settings_general_email_send_as'] == 'html' ? ' selected' : '' ?>> <?php _e('Html', 'better-payment'); ?> </option>
                                                                </select>
                                                            </div>
                                                            <p><?php _e('Html helps to send html markup in the email body. Select plain if you just want plain text in the email body.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="customer-email" class="sidebar__tab__content p50">
                                                <div class="mailing__option mb30">
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('To', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <p class="mt0"><?php _e('Customer email address will be auto populated from payment form. This email will be used to send email notification for each transaction.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Subject', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="text" class="form__control" placeholder="<?php _e('Email subject', 'better-payment'); ?>" name="better_payment_settings_general_email_subject_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_subject_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_subject_customer'] ) : '' ?>">
                                                            <p><?php _e('Email subject for the customer (who make payments) email notification.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Message', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <textarea class="form__control" name="better_payment_settings_general_email_message_customer"><?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_message_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_message_customer'] ) : '' ?> </textarea>
                                                            <p><?php _e('Email body for the customer email notification. ', 'better-payment'); ?> <code>[bp-all-fields]</code> <?php _e(' shortcode can be used to display all the form fields (which was used to make payments).', 'better-payment') ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="#" class="button add__button email-additional-headers"><span><i class="bp-icon bp-plus"></i></span> <?php _e('Additional Headers', 'better-payment'); ?></a>
                                                <div class="mailing__option mt30 email-additional-headers-content">
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('From Name', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="text" class="form__control" name="better_payment_settings_general_email_from_name_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_from_name_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_from_name_customer'] ) : '' ?>">
                                                            <p><?php _e('From name that will be used in the email headers.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('From Email', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_from_email_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_from_email_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_from_email_customer'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as From Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Reply-To', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_reply_to_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_reply_to_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_reply_to_customer'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Reply-To Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Cc', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_cc_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_cc_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_cc_customer'] ) : '' ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Cc Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Bcc', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <input type="email" class="form__control" name="better_payment_settings_general_email_bcc_customer" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_bcc_customer']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_general_email_bcc_customer'] ) : '' ?>" type="email" placeholder="<?php _e('Bcc email', 'better-payment'); ?>">
                                                            <p><?php _e('Email address that will be displayed in the email header as Bcc Email.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="input__wrap">
                                                        <p class="title"><?php _e('Send As', 'better-payment'); ?></p>
                                                        <div class="input__area">
                                                            <div class="bp-select">
                                                                <select name="better_payment_settings_general_email_send_as_customer">
                                                                    <option value="" disabled> <?php _e('Select One', 'better-payment'); ?> </option>
                                                                    <option value="plain" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_send_as_customer']) && $bp_admin_saved_settings['better_payment_settings_general_email_send_as_customer'] == 'plain' ? ' selected' : '' ?>> <?php _e('Plain', 'better-payment'); ?> </option>
                                                                    <option value="html" <?php echo isset($bp_admin_saved_settings['better_payment_settings_general_email_send_as_customer']) && $bp_admin_saved_settings['better_payment_settings_general_email_send_as_customer'] == 'html' ? ' selected' : '' ?>> <?php _e('Html', 'better-payment'); ?> </option>
                                                                </select>
                                                            </div>
                                                            <p><?php _e('Html helps to send html markup in the email body. Select plain if you just want plain text in the email body.', 'better-payment'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="paypal" class="sidebar__tab__content better-payment-settings-payment-paypal">
                                                <div class="payment__options">
                                                    <div class="payment__option">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Mode', 'better-payment'); ?></h4>
                                                            <p><?php _e('Live mode allows you to process real transactions. It just requires live Stripe keys (public and secret keys) to accept real payments.', 'better-payment'); ?></p>
                                                        </div>
                                                        <div class="active__status">
                                                            <label class="bp-switch">
                                                                <input type="hidden" name="better_payment_settings_payment_paypal_live_mode" value="no">
                                                                <input type="checkbox" name="better_payment_settings_payment_paypal_live_mode" value="yes" data-target="better-payment-settings-payment-paypal-live" data-targettest="bp-paypal-test-key" data-targetlive="bp-paypal-live-key" <?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_live_mode']) && $bp_admin_saved_settings['better_payment_settings_payment_paypal_live_mode'] == 'yes' ? ' checked' : '' ?>>
                                                                <span class="switch__btn"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="payment__option">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Business Email', 'better-payment'); ?></h4>
                                                            <p><?php _e('Your PayPal account email address to accept payment via PayPal.', 'better-payment'); ?></p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="email" class="form__control" name="better_payment_settings_payment_paypal_email" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_email']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_paypal_email'] ) : '' ?>">
                                                        </div>
                                                    </div>

                                                    <?php $better_payment_settings_payment_paypal_live = isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_live_mode']) && $bp_admin_saved_settings['better_payment_settings_payment_paypal_live_mode'] == 'yes'; ?>

                                                    <div class="payment__option bp-paypal-key bp-paypal-live-key <?php echo $better_payment_settings_payment_paypal_live ? 'bp-d-block' : 'bp-d-none' ?>">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Client ID', 'better-payment'); ?></h4>
                                                            <p><?php _e('PayPal live client ID is required to do Refund via PayPal. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://developer.paypal.com/developer/applications">https://developer.paypal.com/developer/applications</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="text" class="form__control mt15" name="better_payment_settings_payment_paypal_live_client_id" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_live_client_id']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_paypal_live_client_id'] ) : '' ?>" type="text" placeholder="">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-paypal-key bp-paypal-live-key <?php echo $better_payment_settings_payment_paypal_live ? 'bp-d-block' : 'bp-d-none' ?>">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Secret', 'better-payment'); ?></h4>
                                                            <p><?php _e('PayPal live secret is required to do refund via PayPal. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://developer.paypal.com/developer/applications">https://developer.paypal.com/developer/applications</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="password" class="form__control mt15" name="better_payment_settings_payment_paypal_live_secret" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_live_secret']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_paypal_live_secret'] ) : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-paypal-key bp-paypal-test-key <?php echo $better_payment_settings_payment_paypal_live ? 'bp-d-none' : 'bp-d-block' ?> ">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Test/Sandbox Client ID', 'better-payment'); ?></h4>
                                                            <p><?php _e('PayPal test/sandbox client id is required to do rufund via PayPal. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://developer.paypal.com/developer/applications">https://developer.paypal.com/developer/applications</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="text" class="form__control mt15" name="better_payment_settings_payment_paypal_test_client_id" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_test_client_id']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_paypal_test_client_id'] ) : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-paypal-key bp-paypal-test-key <?php echo $better_payment_settings_payment_paypal_live ? 'bp-d-none' : 'bp-d-block' ?> ">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Test/Sandbox Secret', 'better-payment'); ?></h4>
                                                            <p><?php _e('PayPal test/sandbox secret is required to do refund via PayPal. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://developer.paypal.com/developer/applications">https://developer.paypal.com/developer/applications</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="password" class="form__control mt15" name="better_payment_settings_payment_paypal_test_secret" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_paypal_test_secret']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_paypal_test_secret'] ) : '' ?>">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>

                                            <div id="stripe" class="sidebar__tab__content better-payment-settings-payment-stripe">
                                                <div class="payment__options">
                                                    <div class="payment__option">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Mode', 'better-payment'); ?></h4>
                                                            <p><?php _e('Live mode allows you to process real transactions. It just requires live Stripe keys (public and secret keys) to accept real payments.', 'better-payment'); ?></p>
                                                        </div>
                                                        <div class="active__status">
                                                            <label class="bp-switch">
                                                                <input type="hidden" name="better_payment_settings_payment_stripe_live_mode" value="no">
                                                                <input type="checkbox" name="better_payment_settings_payment_stripe_live_mode" value="yes" data-targettest="bp-stripe-test-key" data-targetlive="bp-stripe-live-key" <?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_live_mode']) && $bp_admin_saved_settings['better_payment_settings_payment_stripe_live_mode'] == 'yes' ? ' checked' : '' ?>>
                                                                <span class="switch__btn"></span>
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <?php $better_payment_settings_payment_stripe_live = isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_live_mode']) && $bp_admin_saved_settings['better_payment_settings_payment_stripe_live_mode'] == 'yes'; ?>

                                                    <div class="payment__option bp-stripe-key bp-stripe-live-key <?php echo $better_payment_settings_payment_stripe_live ? 'bp-d-block' : 'bp-d-none' ?>">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Public Key', 'better-payment'); ?></h4>
                                                            <p><?php _e('Stripe live public key is required to make payments via Stripe. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://stripe.com/docs/keys">https://stripe.com/docs/keys</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="text" class="form__control mt15" name="better_payment_settings_payment_stripe_live_public" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_live_public']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_stripe_live_public'] ) : '' ?>" type="text" placeholder="<?php //_e('Live public key', 'better-payment'); ?>">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-stripe-key bp-stripe-live-key <?php echo $better_payment_settings_payment_stripe_live ? 'bp-d-block' : 'bp-d-none' ?>">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Live Secret Key', 'better-payment'); ?></h4>
                                                            <p><?php _e('Stripe live secret key is required to make payments via Stripe. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://stripe.com/docs/keys">https://stripe.com/docs/keys</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="password" class="form__control mt15" name="better_payment_settings_payment_stripe_live_secret" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_live_secret']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_stripe_live_secret'] ) : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-stripe-key bp-stripe-test-key <?php echo $better_payment_settings_payment_stripe_live ? 'bp-d-none' : 'bp-d-block' ?> ">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Test Public Key', 'better-payment'); ?></h4>
                                                            <p><?php _e('Stripe test public key is required to make payments via Stripe. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://stripe.com/docs/keys">https://stripe.com/docs/keys</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="text" class="form__control mt15" name="better_payment_settings_payment_stripe_test_public" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_test_public']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_stripe_test_public'] ) : '' ?>">
                                                        </div>
                                                    </div>
                                                    <div class="payment__option bp-stripe-key bp-stripe-test-key <?php echo $better_payment_settings_payment_stripe_live ? 'bp-d-none' : 'bp-d-block' ?> ">
                                                        <div class="payment__option__content">
                                                            <h4><?php _e('Test Secret Key', 'better-payment'); ?></h4>
                                                            <p><?php _e('Stripe test secret key is required to make payments via Stripe. For more help visit', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://stripe.com/docs/keys">https://stripe.com/docs/keys</a>.</p>
                                                        </div>
                                                        <div class="active__status input__wrap">
                                                            <input type="password" class="form__control mt15" name="better_payment_settings_payment_stripe_test_secret" value="<?php echo isset($bp_admin_saved_settings['better_payment_settings_payment_stripe_test_secret']) ? esc_attr( $bp_admin_saved_settings['better_payment_settings_payment_stripe_test_secret'] ) : '' ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab__content__item background__white" id="premium">
                                    <div class="main__content__area">
                                        <div class="content__area__body p50">
                                            <div class="go__premium">
                                                <h3><?php _e('Why upgrade to Premium Version?', 'better-payment'); ?></h3>
                                                <p><?php _e('Get access to Analytics, Refund, Invoice & many more features that makes your life way easier. You will also get world class support from our dedicated team 24/7.', 'better-payment'); ?></p>
                                                <a href="#" class="button button__active"><?php _e('Get Premium Version', 'better-payment'); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bp-col-lg-3">
                        <div class="statistic">
                            <div class="icon">
                                <i class="bp-icon bp-swap"></i>
                            </div>
                            <div class="statistic__body">
                                <h3><?php esc_html_e($bp_admin_settings_transactions_all_count, 'better-payment'); ?></h3>
                                <p><?php _e('Total Transactions', 'better-payment'); ?></p>
                            </div>
                        </div>
                        <div class="statistic">
                            <div class="icon">
                                <i class="bp-icon bp-list-check"></i>
                            </div>
                            <div class="statistic__body">
                                <h3><?php esc_html_e($bp_admin_settings_transactions_paid_count, 'better-payment'); ?></h3>
                                <p><?php _e('Completed Transactions', 'better-payment'); ?></p>
                            </div>
                        </div>
                        <div class="statistic">
                            <div class="icon">
                                <i class="bp-icon bp-server"></i>
                            </div>
                            <div class="statistic__body">
                                <h3><?php esc_html_e($bp_admin_settings_transactions_processing_count, 'better-payment'); ?></h3>
                                <p><?php _e('Processing Transactions', 'better-payment'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bp-row">
                    <div class="bp-col-xl-3 bp-col-md-6">
                        <div class="feature__card__wrapper">
                            <div class="feature__card">
                                <div class="icon">
                                    <i class="bp-icon bp-doc"></i>
                                </div>
                                <h3><?php _e('Documentation', 'better-payment'); ?></h3>
                                <p><?php _e('Get started by spending some time with the documentation to get familiar with Better Payment.', 'better-payment'); ?></p>
                                <a href="#" class="button"><?php _e('Documentation', 'better-payment'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="bp-col-xl-3 bp-col-md-6">
                        <div class="feature__card__wrapper">
                            <div class="feature__card">
                                <div class="icon">
                                    <i class="bp-icon bp-contribute"></i>
                                </div>
                                <h3><?php _e('Contribute to Better Payment', 'better-payment'); ?></h3>
                                <p><?php _e('You can contribute to make Better Payment better reporting bugs, creating issues, pull requests at ', 'better-payment'); ?> <a class="color__themeColor" target="_blank" rel="nofollow" href="https://github.com/WPDevelopers">Github</a>.</p>
                                <a href="#" class="button"><?php _e('Report a Bug', 'better-payment'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="bp-col-xl-3 bp-col-md-6">
                        <div class="feature__card__wrapper">
                            <div class="feature__card">
                                <div class="icon">
                                    <i class="bp-icon bp-help-center"></i>
                                </div>
                                <h3><?php _e('Need Help?', 'better-payment'); ?></h3>
                                <p><?php _e('Stuck with something? Get help from live chat or support ticket.', 'better-payment'); ?></p>
                                <a href="https://wpdeveloper.net/support" class="button"><?php _e('Initiate a Chat', 'better-payment'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="bp-col-xl-3 bp-col-md-6">
                        <div class="feature__card__wrapper">
                            <div class="feature__card">
                                <div class="icon">
                                    <i class="bp-icon bp-heart"></i>
                                    <h3><?php _e('Show Your Love', 'better-payment'); ?></h3>
                                </div>

                                <p><?php _e('We love to have you in Better Payment family. We are making it more awesome everyday. Take your 2 minutes to review the plugin and spread the love to encourage us to keep it going.', 'better-payment'); ?></p>
                                <a href="#" class="button"><?php _e('Leave a Review', 'better-payment'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
<!-- Admin Settings Form Wrapper: Ends  -->
