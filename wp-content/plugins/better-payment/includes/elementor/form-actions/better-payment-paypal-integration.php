<?php

defined( 'ABSPATH' ) || exit();


use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Form_Record;

class Better_Payment_Paypal_Integration extends Action_Base {
    private $better_payment_global_settings = [];

    public function get_name() {
        return 'paypal';
    }

    public function get_label() {
        return __( 'PayPal', 'better-payment' );
    }

    /**
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section( $widget ) {
        $this->better_payment_global_settings = Better_Payment_DB::get_settings();

        $widget->start_controls_section(
            'section_paypal_payment',
            [
                'label'     => __( 'PayPal', 'better-payment' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                    'better_payment_pay_amount_enable' => 'yes'
                ],
            ]
        );

        $widget->add_control(
            'better_payment_form_paypal_currency',
            [
                'label'   => esc_html__( 'Currency Symbols', 'better-payment' ),
                'type'    => Controls_Manager::SELECT,
                'default' => esc_html($this->better_payment_global_settings['better_payment_settings_general_general_currency']),
                'options' => [
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
                ]
            ]
        );

        $widget->add_control(
            'better_payment_paypal_business_email',
            [
                'label'       => __( 'Business Email', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $widget->add_control(
            'better_payment_paypal_button_type',
            [
                'label'   => esc_html__( 'Button Type', 'better-payment' ),
                'type'    => Controls_Manager::SELECT,
                'default' => '_xclick',
                'options' => [
                    '_xclick'    => 'XCLICK',
                    '_cart'      => 'CART',
                    '_donations' => 'DONATIONS'
                ]
            ]
        );

        $widget->add_control(
            'better_payment_paypal_live_mode',
            [
                'label'        => __( 'Live Mode', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'no',
            ]
        );
        $widget->end_controls_section();
    }

    /**
     * @param array $element
     * @return array
     */
    public function on_export( $element ) {
        unset(
            $element[ 'settings' ][ 'better_payment_paypal_business_email' ],
            $element[ 'settings' ][ 'better_payment_paypal_button_type' ],
            $element[ 'settings' ][ 'better_payment_paypal_live_mode' ],
            $element[ 'settings' ][ 'better_payment_form_paypal_currency' ]
        );

        return $element;
    }

    /**
     * @param Form_Record $record
     * @param Ajax_Handler $ajax_handler
     */
    public function run( $record, $ajax_handler ) {

        $email       = $record->get_form_settings( 'better_payment_paypal_business_email' );
        $button_type = $record->get_form_settings( 'better_payment_paypal_button_type' );
        $live_mode   = $record->get_form_settings( 'better_payment_paypal_live_mode' );

        if ( $live_mode == 'yes' ) {
            $path = "paypal";
        } else {
            $path = "sandbox.paypal";
        }

        if ( empty( $email ) ) {
            $ajax_handler->add_error_message( 'Business Email is required!' );
            return false;
        }

        // Get submitted Form data
        $sent_data = $record->get( 'sent_data' );

        $el_form_form_settings = $record->get('form_settings');
        $page_id = $el_form_form_settings['form_post_id'] ? $el_form_form_settings['form_post_id'] : ''; 
        $widget_id = $el_form_form_settings['id'] ? $el_form_form_settings['id'] : ''; 

        if ( empty( $sent_data[ 'pay_amount' ] ) ) {
            $ajax_handler->add_error_message( 'Amount field is required!' );
            return false;
        }

        $amount   = floatval($sent_data[ 'pay_amount' ]);
        $quantity = 1;
        if ( !empty( $sent_data[ 'pay_quantity' ] ) ) {
            $quantity = intval( $sent_data[ 'pay_quantity' ] );
        }

        $return_url = get_the_permalink() . '?better_payment_paypal_status=success&better_payment_widget_id=' . sanitize_text_field( $record->get( 'form_settings' )[ 'id' ] );
        $cancel_url = get_the_permalink() . '?better_payment_error_status=error&better_payment_widget_id=' . sanitize_text_field( $record->get( 'form_settings' )[ 'id' ] );
        $order_id   = 'paypal_' . uniqid();

        $request_data = [
            'business'      => $email,
            'currency_code' => $record->get_form_settings( 'better_payment_form_paypal_currency' ),
            'rm'            => '2',
            'return'        => esc_url( $return_url ),
            'cancel_return' => esc_url( $cancel_url ),
            'item_number'   => $order_id,
            'quantity'      => $quantity,
            'item_name'     => 'Test Product',
            'amount'        => $amount ,
            'cmd'           => $button_type,
        ];

        $better_form_fields = [
            'email' => $email,
            'el_form_fields' => maybe_serialize($sent_data),
            'amount' => $record->get_form_settings( 'better_payment_form_paypal_currency' ) . $amount,
            'referer_page_id' => $page_id,
            'referer_widget_id' => $widget_id,
            'source' => 'paypal'
        ];

        Better_Payment_Handler::payment_create(
            [
                'amount'       => $amount,
                'order_id'     => $order_id,
                'payment_date' => date( 'Y-m-d H:i:s' ),
                'source'       => 'paypal',
                'form_fields_info' => maybe_serialize( $better_form_fields ),
                'currency'     => $record->get_form_settings( 'better_payment_form_paypal_currency' ),
                'referer'      => "elementor-form",
            ]
        );
        $paypal_url = "https://www.$path.com/cgi-bin/webscr?";
        $paypal_url .= http_build_query( $request_data );

        if ( !empty( $paypal_url ) && filter_var( $paypal_url, FILTER_VALIDATE_URL ) ) {
            $ajax_handler->add_response_data( 'redirect_url', esc_url_raw($paypal_url) );
        }
    }
}


