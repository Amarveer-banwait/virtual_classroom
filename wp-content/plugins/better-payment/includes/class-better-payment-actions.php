<?php

defined( 'ABSPATH' ) || exit();

class Better_Payment_Actions {
    public function __construct() {
        add_action( 'admin_post_paypal_form_handle', [ $this, 'paypal_form_handle' ] );
        add_action( 'admin_post_nopriv_paypal_form_handle', [ $this, 'paypal_form_handle' ] );

        add_action( 'wp_ajax_better_payment_stripe_get_token', [ $this, 'better_payment_stripe_get_token' ] );
        add_action( 'wp_ajax_nopriv_better_payment_stripe_get_token', [ $this, 'better_payment_stripe_get_token' ] );
    }


    public function paypal_form_handle() {
        check_admin_referer( 'better-payment-paypal', 'security' );

        if ( !empty( $_POST[ 'better_payment_page_id' ] ) ) {
            $page_id = intval( $_POST[ 'better_payment_page_id' ], 10 );
        } else {
            $this->redirect_previous_page();
        }

        if ( !empty( $_POST[ 'better_payment_widget_id' ] ) ) {
            $widget_id = sanitize_text_field( $_POST[ 'better_payment_widget_id' ] );
        } else {
            $this->redirect_previous_page();
        }

        $el_settings = $this->better_payment_widget_settings( $page_id, $widget_id );

        if ( empty( $el_settings ) ) {
            $this->redirect_previous_page();
        }

        if ( empty( $el_settings[ 'better_payment_paypal_business_email' ] ) ) {
            $this->redirect_previous_page();
        }

        if ( $el_settings[ 'better_payment_paypal_live_mode' ] == 'yes' ) {
            $path = "paypal";
        } else {
            $path = "sandbox.paypal";
        }

        $order_id     = 'paypal_' . uniqid();
        $request_data = [
            'business'      => $el_settings[ 'better_payment_paypal_business_email' ],
            'currency_code' => $el_settings[ 'better_payment_form_currency' ],
            'rm'            => '2',
            'return'        => esc_url_raw( $_POST[ 'return' ] ),
            'cancel_return' => esc_url_raw( $_POST[ 'cancel_return' ] ),
            'item_number'   => $order_id,
            'item_name'     => sanitize_text_field( $el_settings[ 'better_payment_form_title' ] ),
            'amount'        => floatval( $_POST[ 'pay_amount' ] ),
            'cmd'           => $el_settings[ 'better_payment_paypal_button_type' ],
        ];

        if ( !empty( $_POST[ 'first_name' ] ) ) {
            $request_data[ 'first_name' ] = sanitize_text_field( $_POST[ 'first_name' ] );
        }

        if ( !empty( $_POST[ 'last_name' ] ) ) {
            $request_data[ 'last_name' ] = sanitize_text_field( $_POST[ 'last_name' ] );
        }

        if ( !empty( $_POST[ 'email' ] ) ) {
            $request_data[ 'email' ] = sanitize_email( $_POST[ 'email' ] );
        }

        //Form fields data to send via email
        $better_form_fields = [
            'first_name' => !empty( $_POST[ 'first_name' ] ) ? sanitize_text_field( $_POST[ 'first_name' ] ) : '' ,
            'last_name' => !empty( $_POST[ 'last_name' ] ) ? sanitize_text_field( $_POST[ 'last_name' ] ) : '',
            'email' => !empty( $_POST[ 'email' ] ) ? sanitize_email( $_POST[ 'email' ] ) : '',
            'amount' => $el_settings[ 'better_payment_form_currency' ] . floatval( $_POST[ 'pay_amount' ] ),
            'referer_page_id' => $page_id,
            'referer_widget_id' => $widget_id,
            'source' => 'paypal'
        ];
        
        Better_Payment_Handler::payment_create(
            [
                'amount'       => floatval( $_POST[ 'pay_amount' ] ),
                'order_id'     => $order_id,
                'payment_date' => date( 'Y-m-d H:i:s' ),
                'source'       => 'paypal',
                'form_fields_info'     => maybe_serialize( $better_form_fields ),
                'currency'     => $el_settings[ 'better_payment_form_currency' ],
                'referer'      => "widget",
            ]
        );
        $paypal_url = "https://www.$path.com/cgi-bin/webscr?";
        $paypal_url .= http_build_query( $request_data );
        wp_redirect( esc_url_raw($paypal_url) );
    }

    public function better_payment_stripe_get_token() {
        if ( !check_admin_referer( 'better-payment', 'security' ) ) {
            wp_send_json_error();
        }

        $setting_data_page_id = isset($_POST[ 'setting_data' ]['page_id']) ? intval( $_POST[ 'setting_data' ]['page_id'] ) : 0;
        $setting_data_widget_id = isset($_POST[ 'setting_data' ]['widget_id']) ? sanitize_text_field( $_POST[ 'setting_data' ]['widget_id'] ) : 0;

        if ( !empty( $setting_data_page_id ) ) {
            $page_id = $setting_data_page_id;
        } else {
            $err_msg = __( 'Page ID is missing', 'better-payment' );
            wp_send_json_error( esc_html($err_msg) );
        }

        if ( !empty( $setting_data_widget_id ) ) {
            $widget_id = sanitize_text_field( $setting_data_widget_id );
        } else {
            $err_msg = __( 'Widget ID is missing', 'better-payment' );
            wp_send_json_error( esc_html($err_msg) );
        }

        $el_settings = $this->better_payment_widget_settings( $page_id, $widget_id );
        if ( empty( $el_settings ) ) {
            wp_send_json_error( esc_html(__( 'Setting Data is missing', 'better-payment' )) );
        }

        $amount = isset($_POST[ 'fields' ][ 'pay_amount' ]) ? floatval($_POST[ 'fields' ][ 'pay_amount' ]) : 0;
        
        if ( empty( $el_settings[ 'better_payment_stripe_public_key' ] ) || empty( $el_settings[ 'better_payment_stripe_secret_key' ] ) ) {
            wp_send_json_error( esc_html(__( 'Stripe Key missing', 'better-payment' )) );
        }

        $header_info = array(
            'Authorization'  => 'Basic ' . base64_encode( sanitize_text_field( $el_settings[ 'better_payment_stripe_secret_key' ] ) . ':' ),
            'Stripe-Version' => '2019-05-16'
        );

        $order_id = 'stripe_' . uniqid();
        $request  = [
            'success_url'                => add_query_arg( [
                'better_payment_stripe_status' => 'success',
                'better_payment_stripe_id'     => $order_id,
                'better_payment_widget_id'     => $widget_id
            ], get_permalink( $setting_data_page_id ) ),
            'cancel_url'                 => add_query_arg( [
                'better_payment_error_status' => 'error',
                'better_payment_stripe_id'    => $order_id,
                'better_payment_widget_id'    => $widget_id
            ], get_permalink( $setting_data_page_id ) ),
            'locale'                     => 'auto',
            'payment_method_types'       => [ 'card' ],
            'client_reference_id'        => time(),
            'billing_address_collection' => 'required',
            'metadata'                   => [
                'order_id' => $order_id
            ],
            'line_items'                 => [
                [
                    'amount'   => ( $amount * 100 ),
                    'currency' => $el_settings[ 'better_payment_form_currency' ],
                    'name'     => $el_settings[ 'better_payment_form_title' ],
                    'quantity' => 1
                ]
            ],
            'payment_intent_data'        => [
                'capture_method' => 'automatic',
                'description'    => $el_settings[ 'better_payment_form_title' ],
                'metadata'       => [
                    'order_id' => $order_id
                ]
            ]
        ];

        $post_data_email = isset($_POST[ 'fields' ][ 'email' ]) ? sanitize_email($_POST[ 'fields' ][ 'email' ]) : '';
        $post_data_first_name = isset($_POST[ 'fields' ][ 'first_name' ]) ? sanitize_text_field($_POST[ 'fields' ][ 'first_name' ]) : '';
        $post_data_last_name = isset($_POST[ 'fields' ][ 'last_name' ]) ? sanitize_text_field($_POST[ 'fields' ][ 'last_name' ]) : '';

        if ( !empty( $post_data_email ) ) {
            $request[ 'customer_email' ]                                        = $post_data_email;
            $request[ 'metadata' ][ 'customer_email' ]                          = $request[ 'customer_email' ];
            $request[ 'payment_intent_data' ][ 'metadata' ][ 'customer_email' ] = $request[ 'customer_email' ];

        }

        if ( !empty( $post_data_first_name ) ) {
            $request[ 'customer_name' ] = $post_data_first_name;
        }

        if ( !empty( $post_data_last_name ) ) {
            $request[ 'customer_name' ] = !empty( $request[ 'customer_name' ] ) ? $request[ 'customer_name' ] . ' ' . $post_data_last_name : $post_data_last_name;
        }

        if ( !empty( $request[ 'customer_name' ] ) ) {
            $request[ 'metadata' ][ 'customer_name' ]                          = $request[ 'customer_name' ];
            $request[ 'payment_intent_data' ][ 'metadata' ][ 'customer_name' ] = $request[ 'customer_name' ];
            unset( $request[ 'customer_name' ] );
        }

        $response = wp_safe_remote_post(
            'https://api.stripe.com/v1/checkout/sessions',
            array(
                'method'  => 'POST',
                'headers' => $header_info,
                'body'    => $request,
                'timeout' => 70,
            )
        );

        //Form fields data to send via email
        $better_form_fields = [
            'first_name' => $post_data_first_name ,
            'last_name' => $post_data_last_name,
            'email' => $post_data_email,
            'amount' => $el_settings[ 'better_payment_form_currency' ]. $amount,
            'referer_page_id' => $page_id,
            'referer_widget_id' => $widget_id,
            'source' => 'stripe'
        ];

        $response_ar = json_decode( $response[ 'body' ] );
        
        if ( !empty( $response_ar->payment_intent ) ) {
            Better_Payment_Handler::payment_create(
                [
                    'amount'         => $amount,
                    'order_id'       => $order_id,
                    'payment_date'   => date( 'Y-m-d H:i:s' ),
                    'source'         => 'stripe',
                    'transaction_id' => sanitize_text_field($response_ar->payment_intent),
                    'customer_info'  => maybe_serialize( $response_ar ),
                    'form_fields_info'  => maybe_serialize( $better_form_fields ),
                    'obj_id'         => sanitize_text_field($response_ar->id),
                    'status'         => sanitize_text_field($response_ar->payment_status),
                    'currency'       => $el_settings[ 'better_payment_form_currency' ],
                    'referer'        => "widget",
                ]
            );
            wp_send_json_success(
                [
                    'stripe_data'       => sanitize_text_field($response_ar->id),
                    'stripe_public_key' => sanitize_text_field( $el_settings[ 'better_payment_stripe_public_key' ] )
                ]
            );
        } else {
            $error_message = 'Something went wrong!';

            if (isset($response_ar->error)){
                $error_message = sanitize_text_field($response_ar->error->message);
            }

            wp_send_json_error( $error_message );
        }
    }

    public function better_payment_widget_settings( $page_id, $widget_id ) {
        $document = \Elementor\Plugin::$instance->documents->get( $page_id );
        $settings = [];
        if ( $document ) {
            $elements    = \Elementor\Plugin::instance()->documents->get( $page_id )->get_elements_data();
            $widget_data = $this->find_element_recursive( $elements, $widget_id );
            $widget      = \Elementor\Plugin::instance()->elements_manager->create_element_instance( $widget_data );
            if ( $widget ) {
                $settings = $widget->get_settings_for_display();
            }
        }
        return $settings;
    }

    public function find_element_recursive( $elements, $form_id ) {

        foreach ( $elements as $element ) {
            if ( $form_id === $element[ 'id' ] ) {
                return $element;
            }

            if ( !empty( $element[ 'elements' ] ) ) {
                $element = $this->find_element_recursive( $element[ 'elements' ], $form_id );

                if ( $element ) {
                    return $element;
                }
            }
        }

        return false;
    }

    public function redirect_previous_page() {
        $location = $_SERVER[ 'HTTP_REFERER' ];
        wp_safe_redirect( $location );
        exit();
    }
    
}

