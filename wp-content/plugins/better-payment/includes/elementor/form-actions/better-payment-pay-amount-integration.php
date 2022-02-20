<?php

defined( 'ABSPATH' ) || exit();


use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Classes\Action_Base;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\Form_Record;

class Better_Payment_Pay_Amount_Integration extends Action_Base {
    private $better_payment_global_settings = [];

    public function get_name() {
        return '';
    }

    public function get_label() {
        return __( 'Better Payment', 'better-payment' );
    }

    /**
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section( $widget ) {
        $this->better_payment_global_settings = Better_Payment_DB::get_settings();

        $widget->start_controls_section(
            'section_better_payment_pay_amount',
            [
                'label'     => __( 'Better Payment', 'better-payment' ),
                'condition' => [
                ],
            ]
        );

        $widget->add_control(
            'better_payment_pay_amount_enable',
            [
                'label'        => __( 'Pay Amount Field', 'better-payment' ),
                'description'        => __( 'We add an extra field type pay amount which offers you to accept payment via stripe and paypal. Disable it if you want to hide the field type.', 'better-payment' ),
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
            $element[ 'settings' ][ 'better_payment_pay_amount_enable' ]
        );

        return $element;
    }

    /**
     * @param Form_Record $record
     * @param Ajax_Handler $ajax_handler
     */
    public function run( $record, $ajax_handler ) {
        //Silence is golden!
    }
}


