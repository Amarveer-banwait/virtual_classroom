<?php
defined( 'ABSPATH' ) || exit();

class Better_Payment_Handler {

    public static function paypal_button( $widget_id = '' , $settings = [] ) {
        global $wp;
        $return_url = add_query_arg( $wp->query_vars, get_the_permalink() . '?better_payment_paypal_status=success&better_payment_widget_id=' . $widget_id );
        $cancel_url = add_query_arg( $wp->query_vars, get_the_permalink() . '?better_payment_error_status=error&better_payment_widget_id=' . $widget_id );
        ob_start();
        $error_info = [
             'business_email' => !empty($settings['better_payment_paypal_business_email'])
        ];

        ?>
        <input type="hidden" name="return" value="<?php echo esc_url( $return_url ); ?>">
        <input type="hidden" name="action" value="paypal_form_handle">
        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'better-payment-paypal' ); ?>">
        <input type="hidden" name="cancel_return" value="<?php echo esc_url( $cancel_url ); ?>">
        <button data-paypal-info = <?php echo esc_attr(json_encode( $error_info) ) ; ?> class="button1 better-payment-paypal-bt"><?php _e( 'PayPal', 'better-payment' ); ?></button>
        <?php
        echo ob_get_clean();
    }

    public static function stripe_button() {
        ?>
        <button class="button1 better-payment-stripe-bt"><?php _e( 'Stripe', 'better-payment' ); ?></button>
        <?php
    }

    public static function payment_create( $data ) {
        global $wpdb;
        $table = "{$wpdb->prefix}better_payment";
        $wpdb->insert( $table, $data );
        if ( $wpdb->insert_id ) {
            return $wpdb->insert_id;
        } else {
            return false;
        }
    }

    public static function manage_response( $settings = [] ) {
        $status = false;

        if ( !empty( $_REQUEST[ 'better_payment_error_status' ] ) ) {
            self::failed_message_template( $settings );
            return $status;
        }

        if ( !empty( $_REQUEST[ 'better_payment_paypal_status' ] ) && $_REQUEST[ 'better_payment_paypal_status' ] == 'success' ) {
            $status = self::paypal_payment_success( $settings );
        } elseif ( !empty( $_REQUEST[ 'better_payment_stripe_status' ] ) && $_REQUEST[ 'better_payment_stripe_status' ] == 'success' ) {
            $status = self::stripe_payment_success( $settings );
        }

        if ( $status ) {
            self::success_message_template( $settings, $status );
        }
        self::remove_arg();

        return $status;
    }

    public static function paypal_payment_success( $settings = [] ) {
        $data = $_REQUEST;
        
        if ( !empty( $data[ 'payment_status' ] ) && !empty( $data[ 'payer_id' ] ) && !empty( $data[ 'payer_status' ] ) ) {
            global $wpdb;
            $table   = "{$wpdb->prefix}better_payment";
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT id,form_fields_info,referer FROM $table WHERE order_id=%s and status is NULL limit 1", sanitize_text_field( $_REQUEST[ 'item_number' ] ) )
            );
            if ( !empty( $results->id ) ) {
                $updated = $wpdb->update(
                    $table,
                    array(
                        'transaction_id' => sanitize_text_field( $data[ 'txn_id' ] ),
                        'status'         => sanitize_text_field( $data[ 'payment_status' ] ),
                        'email'          => sanitize_email( $data[ 'payer_email' ] ),
                        'customer_info'  => maybe_serialize( $data ),
                    ),
                    array( 'ID' => $results->id )
                );
                if ( false !== $updated ) {
                    //Send email notification
                    if ( 
                        ( isset($settings[ 'better_payment_form_email_enable' ]) && $settings[ 'better_payment_form_email_enable' ] == 'yes' ) 
                        || ( $results->referer === 'elementor-form' )
                        ) {
                        self::better_email_notification(sanitize_text_field( $data[ 'txn_id' ] ), sanitize_email( $data[ 'payer_email' ] ), $settings, 'PayPal', $results->form_fields_info);
                    }

                    return sanitize_text_field( $data[ 'txn_id' ] );
                }
            }
        }else if($data['better_payment_paypal_status'] === 'success'){
            return ( isset($data[ 'txn_id' ]) && !empty( sanitize_text_field( $data[ 'txn_id' ] ) ) ) ? sanitize_text_field( $data[ 'txn_id' ] ) : __('Payment under processing!', 'better-payment');
        }
        return false;
    }

    public static function stripe_payment_success( $settings = [] ) {

        $data = $_REQUEST;

        if ( !empty( $data[ 'better_payment_stripe_id' ] ) ) {
            global $wpdb;
            $table   = "{$wpdb->prefix}better_payment";
            $results = $wpdb->get_row(
                $wpdb->prepare( "SELECT id,obj_id,transaction_id,form_fields_info,referer FROM $table WHERE order_id=%s and status = 'unpaid' limit 1", sanitize_text_field( $data[ 'better_payment_stripe_id' ] ) )
            );
            
            if ( !empty( $results->obj_id ) ) {

                $header = array(
                    'Authorization'  => 'Basic ' . base64_encode( 'sk_test_w3EkC2jHywBiypu6a3jYutgZ:' ),
                    'Stripe-Version' => '2019-05-16',
                );

                $request = [
                    'expand' => [
                        'subscription.latest_invoice.payment_intent',
                        'payment_intent'
                    ]
                ];

                $response = wp_safe_remote_post(
                    'https://api.stripe.com/v1/checkout/sessions/' . $results->obj_id,
                    array(
                        'method'  => 'GET',
                        'headers' => $header,
                        'timeout' => 70,
                        'body'    => $request
                    )
                );

                if ( is_wp_error( $response ) || empty( $response[ 'body' ] ) ) {
                    return new WP_Error( 'stripe_error', __( 'There was a problem connecting to the Stripe API endpoint.', 'better-payment' ) );
                }

                $response = json_decode( $response[ 'body' ] );

                if ( isset( $response->error->message ) ) {
                    return new WP_Error( 'stripe_error', __( 'There was a problem connecting to the Stripe API endpoint.', 'better-payment' ) );
                }

                $updated = $wpdb->update(
                    $table,
                    array(
                        'status'        => sanitize_text_field( $response->payment_status ),
                        'email'         => sanitize_email( current( $response->payment_intent->charges->data )->billing_details->email ),
                        'customer_info' => maybe_serialize( $response ),
                    ),
                    array( 'ID' => $results->id )
                );

                if ( false !== $updated ) {
                    //Send email notification
                    $better_customer_email = sanitize_email( current( $response->payment_intent->charges->data )->billing_details->email );
                    if ( 
                        (isset($settings[ 'better_payment_form_email_enable' ]) && $settings[ 'better_payment_form_email_enable' ] == 'yes' ) 
                        || ( $results->referer === 'elementor-form' )
                        ) {
                        self::better_email_notification($results->transaction_id, $better_customer_email, $settings, 'Stripe', $results->form_fields_info);                    
                    }

                    return $results->transaction_id;
                }
            }
        }
        return false;
    }

    public static function get_currency_symbols( $currency = 'USD' ) {
        $list = [
            'AUD' => "$",
            'CAD' => "$",
            'CZK' => "Kč",
            'DKK' => "kr",
            'EUR' => "€",
            'HKD' => "$",
            'HUF' => "ft",
            'ILS' => "₪",
            'JPY' => "¥",
            'MXN' => "$",
            'NOK' => "kr",
            'NZD' => "$",
            'PHP' => "₱",
            'PLN' => "zł",
            'GBP' => "£",
            'RUB' => "₽",
            'SGD' => "$",
            'SEK' => "kr",
            'CHF' => "CHF",
            'TWD' => "$",
            'THB' => "฿",
            'TRY' => "₺",
            'USD' => "$",
        ];
        return !empty( $list[ $currency ] ) ? $list[ $currency ] : '$';
    }

    public static function success_message_template( $settings = [], $tr_id = '' ) {
        if ( empty( $tr_id ) || is_wp_error($tr_id)) {
            return false;
        }

        $image_url = BETTER_PAYMENT_URL . 'asset/img/success.svg';
        $show_icon = 0;

        if ( !empty( $settings[ 'better_payment_form_success_message_icon' ][ 'library' ] ) ) {
            if ( $settings[ 'better_payment_form_success_message_icon' ][ 'library' ] == 'svg' ) {
                $image_url = $settings[ 'better_payment_form_success_message_icon' ][ 'value' ][ 'url' ];
            } else {
                $show_icon = 1;
                $image_url = $settings[ 'better_payment_form_success_message_icon' ][ 'value' ];
            }
        }
        ?>
        <div class="pt140 pb140 background__grey2">
            <div class="better-payment-payment__report better-payment-success-report">
                <div class="report__thumb">
                    <?php if($show_icon) : ?>
                        <i class="<?php echo esc_attr($image_url); ?>"></i>
                    <?php else : ?>    
                        <img src="<?php echo esc_url($image_url); ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="report__content">
                    <h3 class="transaction__success"><?php echo ( isset( $settings[ 'better_payment_form_success_message_heading' ] ) ) ? esc_html( $settings[ 'better_payment_form_success_message_heading' ] ) : __( 'Payment Complete', 'better-payment' ); ?> </h3>
                    <p class="transaction__number"><?php printf( "%s : %s", ( isset( $settings[ 'better_payment_form_success_message_transaction' ] ) ) ? esc_html( $settings[ 'better_payment_form_success_message_transaction' ] ) : __( 'Transaction Number', 'better-payment' ), $tr_id );; ?></p>
                    <p class="payment__greeting"><?php echo ( isset( $settings[ 'better_payment_form_success_message_thanks' ] ) ) ? esc_html( $settings[ 'better_payment_form_success_message_thanks' ] ) : __( 'Thank you for donation.', 'better-payment' ); ?></p>
                </div>
            </div>
        </div>
        <?php
    }


    public static function failed_message_template( $settings = [] ) {

        $image_url = BETTER_PAYMENT_URL . 'asset/img/fail.svg';
        $show_icon = 0;

        if ( !empty( $settings[ 'better_payment_form_error_message_icon' ][ 'library' ] ) ) {
            if ( $settings[ 'better_payment_form_error_message_icon' ][ 'library' ] == 'svg' ) {
                $image_url = $settings[ 'better_payment_form_error_message_icon' ][ 'value' ][ 'url' ];
            } else {
                $show_icon = 1;
                $image_url = $settings[ 'better_payment_form_error_message_icon' ][ 'value' ];
            }
        }
        ?>
        <div class="pb140 background__grey2">
            <div class="better-payment-payment__report better-payment-error-report">
                <div class="report__thumb">
                    <?php if($show_icon) : ?>
                        <i class="<?php echo esc_attr($image_url); ?>"></i>
                    <?php else : ?>    
                        <img src="<?php echo esc_url($image_url); ?>" alt="">
                    <?php endif; ?>
                </div>
                <div class="report__content">
                    <h3 class="transaction__failed"><?php echo ( isset( $settings[ 'better_payment_form_error_message_heading' ] ) ) ? esc_html( $settings[ 'better_payment_form_error_message_heading' ] ) : __( 'Payment Failed', 'better-payment' ); ?></h3>
                </div>
            </div>
        </div>
        <?php
    }

    public static function remove_arg() {
        ?>
        <script>

            const params = new URLSearchParams(location.search);
            if (params.has('better_payment_paypal_status') || params.has('better_payment_stripe_status')) {
                params.delete('better_payment_paypal_status');
                params.delete('better_payment_stripe_status');
                params.delete('better_payment_widget_id');
                params.delete('better_payment_stripe_id');
                window.history.replaceState({}, '', `${location.pathname}?${params}`);
            }
        </script>
        <?php
    }

    public static function better_email_notification($transaction_id, $customer_email, $settings, $referrer='Stripe', $form_fields_info=''){
        //Send Email: customer and admin
        $better_txn_id = sanitize_text_field( $transaction_id );
        $better_cus_email = sanitize_email( $customer_email );
        $better_payment_global_settings = Better_Payment_DB::get_settings();

        $better_payment_email_to = !empty($better_payment_global_settings['better_payment_settings_general_email_to']) ? $better_payment_global_settings['better_payment_settings_general_email_to'] : get_option( 'admin_email' );
        $better_payment_email_subject = !empty($better_payment_global_settings['better_payment_settings_general_email_subject']) ? $better_payment_global_settings['better_payment_settings_general_email_subject'] :  __('Better Payment Transaction', 'better-payment');
        $better_payment_email_content = !empty($better_payment_global_settings['better_payment_settings_general_email_message_admin']) ? $better_payment_global_settings['better_payment_settings_general_email_message_admin'] : '';
        $better_payment_email_from = !empty($better_payment_global_settings['better_payment_settings_general_email_from_email']) ? $better_payment_global_settings['better_payment_settings_general_email_from_email'] : '';
        $better_payment_email_from_name = !empty($better_payment_global_settings['better_payment_settings_general_email_from_name']) ? $better_payment_global_settings['better_payment_settings_general_email_from_name'] : '';
        $better_payment_email_reply_to = !empty($better_payment_global_settings['better_payment_settings_general_email_reply_to']) ? $better_payment_global_settings['better_payment_settings_general_email_reply_to'] : '';
        $better_payment_email_cc = !empty($better_payment_global_settings['better_payment_settings_general_email_cc']) ? $better_payment_global_settings['better_payment_settings_general_email_cc'] : '';
        $better_payment_email_bcc = !empty($better_payment_global_settings['better_payment_settings_general_email_bcc']) ? $better_payment_global_settings['better_payment_settings_general_email_bcc'] : '';
        $better_payment_email_content_type = !empty($better_payment_global_settings['better_payment_settings_general_email_send_as']) ? $better_payment_global_settings['better_payment_settings_general_email_send_as'] : 'html';
        
        $better_payment_email_subject_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_subject_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_subject_customer'] : __('Better Payment Transaction', 'better-payment');
        $better_payment_email_content_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_message_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_message_customer'] : '';
        $better_payment_email_from_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_from_email_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_from_email_customer'] : '';
        $better_payment_email_from_name_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_from_name_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_from_name_customer'] : '';
        $better_payment_email_reply_to_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_reply_to_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_reply_to_customer'] : '';
        $better_payment_email_cc_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_cc_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_cc_customer'] : '';
        $better_payment_email_bcc_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_bcc_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_bcc_customer'] : '';
        $better_payment_email_content_type_customer = !empty($better_payment_global_settings['better_payment_settings_general_email_send_as_customer']) ? $better_payment_global_settings['better_payment_settings_general_email_send_as_customer'] : 'html';

        if(isset($settings['better_payment_email_to'])){
            $better_payment_email_to = sanitize_email($settings['better_payment_email_to']);
        }

        if(isset($settings['better_payment_email_subject'])){
            $better_payment_email_subject = sanitize_text_field($settings['better_payment_email_subject']);
        }

        if(isset($settings['better_payment_email_subject_customer'])){
            $better_payment_email_subject_customer = sanitize_text_field($settings['better_payment_email_subject_customer']);
        }

        if(isset($settings['better_payment_email_content_type'])){
            $better_payment_email_content_type = sanitize_text_field($settings['better_payment_email_content_type']);
        }
        
        if(isset($settings['better_payment_email_content_type_customer'])){
            $better_payment_email_content_type_customer = sanitize_text_field($settings['better_payment_email_content_type_customer']);
        }

		$line_break = $better_payment_email_content_type == 'html' ? '<br>' : "\n";
		$line_break_customer = $better_payment_email_content_type_customer == 'html' ? '<br>' : "\n";

        if(isset($settings['better_payment_email_content'])){
            $better_payment_email_content = sanitize_text_field($settings['better_payment_email_content']);
            $better_payment_email_content = self::better_render_email_body( $better_payment_email_content, $form_fields_info, $line_break );
        } else if ( !empty($better_payment_email_content) ){
            $better_payment_email_content = self::better_render_email_body( $better_payment_email_content, $form_fields_info, $line_break );
        }

        if(isset($settings['better_payment_email_content_customer'])){
            $better_payment_email_content_customer = sanitize_text_field($settings['better_payment_email_content_customer']);
            $better_payment_email_content_customer = self::better_render_email_body( $better_payment_email_content_customer, $form_fields_info, $line_break_customer, 'customer' );
        } else if ( !empty($better_payment_email_content_customer) ){
            $better_payment_email_content_customer = self::better_render_email_body( $better_payment_email_content_customer, $form_fields_info, $line_break, 'customer' ); //$line_break_customer not used : need to check type on parent method and add headers accordingly.
        }

        if(isset($settings['better_payment_email_from'])){
            $better_payment_email_from = sanitize_email($settings['better_payment_email_from']);
        }

        if(isset($settings['better_payment_email_from_name'])){
            $better_payment_email_from_name = sanitize_text_field($settings['better_payment_email_from_name']);
        }

        if(isset($settings['better_payment_email_reply_to'])){
            $better_payment_email_reply_to = sanitize_email($settings['better_payment_email_reply_to']);
        }

        if(isset($settings['better_payment_email_cc'])){
            $better_payment_email_cc = sanitize_email($settings['better_payment_email_cc']);
        }

        if(isset($settings['better_payment_email_bcc'])){
            $better_payment_email_bcc = sanitize_email($settings['better_payment_email_bcc']);
        }

        //Customer Email
        if(isset($settings['better_payment_email_from_customer'])){
            $better_payment_email_from_customer = sanitize_email($settings['better_payment_email_from_customer']);
        }

        if(isset($settings['better_payment_email_from_name_customer'])){
            $better_payment_email_from_name_customer = sanitize_text_field($settings['better_payment_email_from_name_customer']);
        }

        if(isset($settings['better_payment_email_reply_to_customer'])){
            $better_payment_email_reply_to_customer = sanitize_email($settings['better_payment_email_reply_to_customer']);
        }

        if(isset($settings['better_payment_email_cc_customer'])){
            $better_payment_email_cc_customer = sanitize_email($settings['better_payment_email_cc_customer']);
        }

        if(isset($settings['better_payment_email_bcc_customer'])){
            $better_payment_email_bcc_customer = sanitize_email($settings['better_payment_email_bcc_customer']);
        }

        if($better_payment_email_content == ''){
            $better_payment_email_content = __('New better payment transaction! ', 'better-payment');
        }

        if($better_payment_email_content_customer == ''){
            $better_payment_email_content_customer = __('New better payment transaction! ', 'better-payment');
        }

        $better_payment_email_headers = [];
        $better_payment_email_headers_customer = [];

        if($better_payment_email_content_type == 'html'){
            $better_payment_email_headers[] = 'Content-Type: text/html; charset=UTF-8';
            $better_payment_email_headers_customer[] = 'Content-Type: text/html; charset=UTF-8';
        }

        if($better_payment_email_from != ''){
            $better_payment_email_headers[] = "From: $better_payment_email_from_name <$better_payment_email_from>";
        }

        if($better_payment_email_cc != ''){
            $better_payment_email_headers[] = "Cc: <$better_payment_email_cc>";
        }

        if($better_payment_email_bcc != ''){
            $better_payment_email_headers[] = "BCc: $better_payment_email_bcc";
        }

        if($better_payment_email_reply_to != ''){
           $better_payment_email_headers[] = "Reply-To: $better_payment_email_reply_to";
        }

        //Customer Email
        if($better_payment_email_from_customer != ''){
            $better_payment_email_headers_customer[] = "From: $better_payment_email_from_name_customer <$better_payment_email_from_customer>";
        }

        if($better_payment_email_cc_customer != ''){
            $better_payment_email_headers_customer[] = "Cc: <$better_payment_email_cc_customer>";
        }

        if($better_payment_email_bcc_customer != ''){
            $better_payment_email_headers_customer[] = "BCc: $better_payment_email_bcc_customer";
        }

        if($better_payment_email_reply_to_customer != ''){
           $better_payment_email_headers_customer[] = "Reply-To: $better_payment_email_reply_to_customer";
        }

        self::send_better_email($better_cus_email, $better_payment_email_subject_customer, $better_payment_email_content_customer, $better_payment_email_headers_customer); //customer mail
        self::send_better_email($better_payment_email_to, $better_payment_email_subject, $better_payment_email_content, $better_payment_email_headers); //admin mail
    }

    /**
     * Sends an HTML email.
     *
     * @since  0.0.2
     *
     * @param string $to
     * @param string $subject
     * @param array  $message
     *
     * @return bool false indicates mail sending failed while true doesn't gurantee that mail sent.
     * true just indicate script processed successfully without any errors. 
     * Ref: https://developer.wordpress.org/reference/functions/wp_mail/
     */
    public static function send_better_email( $to, $subject, $message, $headers=[]) {
        $response = false;

        $mailValidated = filter_var($to, FILTER_VALIDATE_EMAIL);

        if ($mailValidated) {
            $response = wp_mail( $to, $subject, $message, $headers );
        }

        return $response;
    }

    public static function better_render_email_body( $email_body, $form_fields_info, $line_break, $type = 'admin' ) {
		$is_empty_email_body = empty($email_body);

        $email_body = do_shortcode( $email_body );
        $bp_form_fields_html_content = $email_body;
        
        $bp_all_fields_shortcode = 'bp-all-fields';
        $referer_content_page_link = '#';
		
        $field_text = __('Field', 'better-payment');
        $value_text = __('Entry', 'better-payment');

        $bp_form_fields_html_header = '<table style="margin-bottom: 20px; font-size: 14px; border-collapse: collapse; width: 100%;">
        <tr style="background-color: #6E58F7; color: #fff;">
            <td style="padding: 10px; border-left: 1px solid #6E58F7; border-right: 1px solid rgba(161, 161, 161, 0.5);">
            '.$field_text.'
            </th>
            <td style="padding: 10px; border-left: 1px solid rgba(161, 161, 161, 0.5); border-right: 1px solid #6E58F7;">
            '.$value_text.'
            </th>
        </tr>';

        $bp_form_fields_html_footer = "</table>";

        if ( false !== strpos( $email_body, $bp_all_fields_shortcode ) ) {
			$content = ''. $line_break;
            $form_fields_info_arr = maybe_unserialize($form_fields_info);
			
            foreach ( $form_fields_info_arr as $key => $field ) {
                //Hide few fields
                if($key === 'referer_page_id' || $key === 'referer_widget_id' || $key === 'source' || $key === 'el_form_fields') {
                    if($key === 'referer_page_id'){
                        $referer_content_page_link = !empty($field) ? get_permalink( $field ) : $referer_content_page_link;
                    }

                    continue;
                }

                $key_formatted = self::better_title_case($key);
                $content .= "<tr>
                                <td style='padding: 7px; border: 1px solid rgba(161, 161, 161, 0.1);'> $key_formatted </td>
                                <td style='padding: 7px; border: 1px solid rgba(0, 0, 0, .1);'> $field </td>
                            </tr>";
			}

            $bp_form_fields_html_body = $content;
            $bp_form_fields_html_content = $bp_form_fields_html_header . $bp_form_fields_html_body . $bp_form_fields_html_footer ;

            //Replace shortcode with form fields info
			$email_body = str_replace( "[$bp_all_fields_shortcode]", $bp_form_fields_html_content, $email_body );
		}

        ob_start();
		include BETTER_PAYMENT_ADMIN_VIEW_PATH . "template-email-notification.php";
        $email_body = ob_get_contents();
        ob_end_clean();

		return $email_body;
	}

    public static function better_title_case($string){
        $string = str_replace('_',' ', $string);
        $string = ucwords($string);
        return $string;
    }

}
