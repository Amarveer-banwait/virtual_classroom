<?php

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit();

class Better_Payment_EL_integration {

    private $pay_amount = 'pay_amount';

    public function init() {
        add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widget' ) );
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'elementor_form_integration' ], 10 );
        add_action( 'elementor-pro/forms/pre_render', [ $this, 'elementor_pro_form_response' ], 10, 2 );
    }

    public function register_widget( $widgets_manager ) {
        require 'better-payment-widget.php';
        $widgets_manager->register_widget_type( new Better_Payment_Widget );
    }

    public function elementor_form_integration() {
        if(!defined('ELEMENTOR_PRO_VERSION')){
            return false;
        }
        require 'form-actions/better-payment-pay-amount-integration.php';
        require 'form-actions/better-payment-stripe-integration.php';
        require 'form-actions/better-payment-paypal-integration.php';
        require 'form-actions/better-payment-pay-amount-field.php';

        ElementorPro\Modules\Forms\Module::instance()->add_form_action( 'better-payment', new Better_Payment_Pay_Amount_Integration() ); 
        ElementorPro\Modules\Forms\Module::instance()->add_form_action( 'Stripe', new Better_Payment_Stripe_Integration() );
        ElementorPro\Modules\Forms\Module::instance()->add_form_action( 'PayPal', new Better_Payment_Paypal_Integration() );
        ElementorPro\Modules\Forms\Module::instance()->add_form_field_type( 'pay_amount', new Better_Payment_Pay_Amount_Field() );
    }

    public function elementor_pro_form_response( $settings, $obj ) {
        wp_enqueue_style( 'better-payment-el' );
        $response = Better_Payment_Handler::manage_response( $settings );
        if ( $response ) {
            return false;
        }
    }

}

