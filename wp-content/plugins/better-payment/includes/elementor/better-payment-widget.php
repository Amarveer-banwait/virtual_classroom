<?php
defined( 'ABSPATH' ) || exit();

use \Elementor\Controls_Manager as Controls_Manager;
use Elementor\Core\Schemes\Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use \Elementor\Repeater;
use Elementor\Utils;
use \Elementor\Widget_Base as Widget_Base;

class Better_Payment_Widget extends Widget_Base {

    private $better_payment_global_settings = [];

    public function get_name() {
        return 'better-payment';
    }

    public function get_title() {
        return esc_html__( 'Better Payment', 'better-payment' );
    }

    public function get_icon() {
        return 'bp-icon bp-logo';
    }

    public function get_keywords() {
        return [
            'payment', 'better-payment' ,'paypal', 'stripe', 'sell', 'donate', 'transaction', 'online-transaction'
        ];
    }

    public function get_style_depends() {
        return [ 'better-payment-el' ];
    }

    protected function _register_controls() {
        
        $this->better_payment_global_settings = Better_Payment_DB::get_settings();

        $this->start_controls_section(
            'better_payment_form_setting',
            [
                'label' => esc_html__( 'Payment Settings', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_form_title',
            [
                'label'       => __( 'Form Title', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true,
                'default'     => __( 'Form Title', 'better-payment' )
            ]
        );

        $this->add_control(
            'better_payment_form_paypal_enable',
            [
                'label'        => __( 'PayPal Enable', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => esc_html($this->better_payment_global_settings['better_payment_settings_general_general_paypal']), //yes or no
            ]
        );

        $this->add_control(
            'better_payment_form_stripe_enable',
            [
                'label'        => __( 'Stripe Enable', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => esc_html($this->better_payment_global_settings['better_payment_settings_general_general_stripe']), //yes or no
            ]
        );
        
        $this->add_control(
            'better_payment_form_email_enable',
            [
                'label'        => __( 'Email Enable', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => esc_html($this->better_payment_global_settings['better_payment_settings_general_general_email']), //yes or no
            ]
        );

        $this->add_control(
            'better_payment_form_currency',
            [
                'label'      => esc_html__( 'Currency Symbols', 'better-payment' ),
                'type'       => Controls_Manager::SELECT,
                'default'    => esc_html($this->better_payment_global_settings['better_payment_settings_general_general_currency']), //USD
                'options'    => [
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
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'name'  => 'better_payment_form_stripe_enable',
                            'value' => 'yes',
                        ],
                        [
                            'name'  => 'better_payment_form_paypal_enable',
                            'value' => 'yes',
                        ],
                    ],
                ]
            ]
        );

        $this->end_controls_section();

        $this->form_element_settings();
        $this->paypal_form_setting();
        $this->stripe_form_setting();
        $this->email_element_settings();

        $this->success_message_setting();
        $this->error_message_setting();

        $this->form_style();
    }

    public function form_element_settings() {
        $this->start_controls_section(
            'better_payment_form_element',
            [
                'label'      => esc_html__( 'Form Settings', 'better-payment' ),
                'conditions' => [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'name'  => 'better_payment_form_stripe_enable',
                            'value' => 'yes',
                        ],
                        [
                            'name'  => 'better_payment_form_paypal_enable',
                            'value' => 'yes',
                        ],
                    ]
                ]
            ]
        );

        $this->add_control(
            'better_payment_first_name_heading',
            [
                'label' => __( 'First Name', 'better-payment' ),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'better_payment_first_name_placeholder',
            [
                'label'       => __( 'Placeholder Text', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'First Name', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'better_payment_first_name_show',
            [
                'label'        => __( 'Show', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'yes',

            ]
        );

        $this->add_control(
            'better_payment_first_name_required',
            [
                'label'        => __( 'Required', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'better_payment_first_name_show' => 'yes'
                ],
                'separator'    => 'after',
            ]
        );

        $this->add_control(
            'better_payment_last_heading',
            [
                'label' => __( 'Last Name', 'better-payment' ),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'better_payment_last_name_placeholder',
            [
                'label'       => __( 'Placeholder', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Last Name', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->add_control(
            'better_payment_last_name_show',
            [
                'label'        => __( 'Show', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'better_payment_last_name_required',
            [
                'label'        => __( 'Required', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'better_payment_last_name_show' => 'yes'
                ],
                'separator'    => 'after',
            ]
        );

        $this->add_control(
            'better_payment_email_heading',
            [
                'label' => __( 'Email', 'better-payment' ),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'better_payment_email_placeholder',
            [
                'label'       => __( 'Placeholder', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Email', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->add_control(
            'better_payment_email_show',
            [
                'label'        => __( 'Show', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'better_payment_email_required',
            [
                'label'        => __( 'Required', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'condition'    => [
                    'better_payment_email_show' => 'yes'
                ],
                'separator'    => 'after',
            ]
        );

        $repeater = new Repeater();


        $repeater->add_control(
            'better_payment_amount_val',
            [
                'label' => esc_html__( 'Amount', 'better-payment' ),
                'type'  => Controls_Manager::NUMBER,
                'min'   => 1,
            ]
        );

        $this->add_control(
            'better_payment_show_amount_list',
            [
                'label'        => __( 'Show Amount List', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'separator'    => 'before',
            ]
        );

        $this->add_control(
            'better_payment_amount',
            [
                'label'       => esc_html__( 'Amount List', 'better-payment' ),
                'type'        => Controls_Manager::REPEATER,
                'default'     => [
                    [
                        'better_payment_amount_val' => 5
                    ],
                    [
                        'better_payment_amount_val' => 10
                    ],
                    [
                        'better_payment_amount_val' => 15
                    ],
                ],
                'fields'      => $repeater->get_controls(),
                'title_field' => '<i class="{{ better_payment_amount_val }}" aria-hidden="true"></i> {{{ better_payment_amount_val }}}',
                'condition'   => [
                    'better_payment_show_amount_list' => 'yes'
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function paypal_form_setting() {
        $this->start_controls_section(
            'better_payment_form_paypal_settings',
            [
                'label'     => esc_html__( 'PayPal Settings', 'better-payment' ),
                'condition' => [
                    'better_payment_form_paypal_enable' => 'yes'
                ]
            ]
        );


        $this->add_control(
            'better_payment_paypal_business_email',
            [
                'label'       => __( 'Business Email', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true,
                'default' => esc_html($this->better_payment_global_settings['better_payment_settings_payment_paypal_email']),
            ]
        );

        $this->add_control(
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

        $this->add_control(
            'better_payment_paypal_live_mode',
            [
                'label'        => __( 'Live Mode', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => esc_html($this->better_payment_global_settings['better_payment_settings_payment_paypal_live_mode']), //yes or no
            ]
        );

        $this->end_controls_section();
    }

    public function stripe_form_setting() {
        $this->start_controls_section(
            'better_payment_form_stripe_settings',
            [
                'label'     => esc_html__( 'Stripe Settings', 'better-payment' ),
                'condition' => [
                    'better_payment_form_stripe_enable' => 'yes'
                ]
            ]
        );
        
        $better_payment_is_stripe_live = $this->better_payment_global_settings['better_payment_settings_payment_stripe_live_mode'] == 'yes' ? 1 : 0;
        
        $this->add_control(
            'better_payment_stripe_public_key',
            [
                'label'       => __( 'Public Key', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true,
                'default'     => $better_payment_is_stripe_live ? esc_html($this->better_payment_global_settings['better_payment_settings_payment_stripe_live_public']) : esc_html($this->better_payment_global_settings['better_payment_settings_payment_stripe_test_public']),
            ]
        );

        $this->add_control(
            'better_payment_stripe_secret_key',
            [
                'label'       => __( 'Secret Key', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'input_type'  => 'password',
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true,
                'default'     => $better_payment_is_stripe_live ? esc_html($this->better_payment_global_settings['better_payment_settings_payment_stripe_live_secret']) : esc_html($this->better_payment_global_settings['better_payment_settings_payment_stripe_test_secret']), 
            ]
        );

        $this->add_control(
            'better_payment_stripe_live_mode',
            [
                'label'        => __( 'Live Mode', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
                'default'      => esc_html($this->better_payment_global_settings['better_payment_settings_payment_paypal_live_mode']), //yes or no
            ]
        );

        $this->end_controls_section();
    }

    public function email_element_settings() {
        $this->start_controls_section(
            'better_payment_email_element',
            [
                'label'      => esc_html__( 'Email Settings', 'better-payment' ),
                'condition' => [
                    'better_payment_form_email_enable' => 'yes'
                ]
            ]
        );

        $this->start_controls_tabs( 'better_payment_email_tabs' );

        //Admin Email Notification
        $this->start_controls_tab( 'better_payment_email_admin_tab', [
			'label' => __( 'Admin', 'better-payment' ),
		] );

        $this->add_control('better_payment_email_to', [
            'label' => __('To', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_to']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_to']) : esc_html(get_option('admin_email')),
            'placeholder' => get_option('admin_email'),
            'label_block' => true,
            'title' => __('Email address to notify site admin after each successful transaction', 'better-payment'),
            'render_type' => 'none'
        ]);

        $default_subject = !empty($this->better_payment_global_settings['better_payment_settings_general_email_subject']) ? esc_html__($this->better_payment_global_settings['better_payment_settings_general_email_subject'], 'better-payment') : sprintf(__('Better Payment transaction on "%s"', 'better-payment'), esc_html(get_option('blogname')));

        $this->add_control('better_payment_email_subject', [
            'label' => __('Subject', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html($default_subject),
            'placeholder' => esc_html($default_subject),
            'label_block' => true,
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_content', [
            'label' => __('Message', 'better-payment'),
            'type' => Controls_Manager::TEXTAREA,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_message_admin']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_message_admin']) : '[bp-all-fields]',
            'placeholder' => '[bp-all-fields]',
            'render_type' => 'none'
        ]);
        
        $site_url_parsed = parse_url(get_site_url());
        $better_domain = $site_url_parsed['host'] ? esc_html($site_url_parsed['host']) : 'example.com';
         
        $this->add_control('better_payment_email_from', [
            'label' => __('From email', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_from_email']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_from_email']) : "wordpress@$better_domain",
            'render_type' => 'none',
            'separator' => 'before'
        ]);

        $this->add_control('better_payment_email_from_name', [
            'label' => __('From name', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_from_name']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_from_name']) : esc_html(get_bloginfo('name')),
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_reply_to', [
            'label' => __('Reply-To', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_reply_to']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_reply_to']) : "wordpress@$better_domain",
            'placeholder' => "wordpress@$better_domain",
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_cc', [
            'label' => __('Cc', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_cc']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_cc']) : '',
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_bcc', [
            'label' => __('Bcc', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_bcc']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_bcc']) : '',
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_content_type', [
            'label' => __('Send as', 'better-payment'),
            'type' => Controls_Manager::SELECT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_send_as']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_send_as']) : 'html', //html or plain
            'render_type' => 'none',
            'options' => [
                'html' => __('HTML', 'better-payment'),
                'plain' => __('Plain', 'better-payment')
            ]
        ]);

		$this->end_controls_tab();

        //Customer Email Notification
        $this->start_controls_tab( 'better_payment_email_customer_tab', [
			'label' => __( 'Customer', 'better-payment' ),
		] );

        $default_subject = !empty($this->better_payment_global_settings['better_payment_settings_general_email_subject_customer']) ? esc_html__($this->better_payment_global_settings['better_payment_settings_general_email_subject_customer'], 'better-payment') : sprintf(__('Better Payment transaction on "%s"', 'better-payment'), esc_html(get_option('blogname')));

        $this->add_control('better_payment_email_subject_customer', [
            'label' => __('Subject', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html($default_subject),
            'placeholder' => esc_html($default_subject),
            'label_block' => true,
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_content_customer', [
            'label' => __('Message', 'better-payment'),
            'type' => Controls_Manager::TEXTAREA,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_message_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_message_customer']) : '[bp-all-fields]',
            'placeholder' => '[bp-all-fields]',
            'render_type' => 'none'
        ]);
        
        $site_url_parsed = parse_url(get_site_url());
        $better_domain = $site_url_parsed['host'] ? esc_html($site_url_parsed['host']) : 'example.com';
         
        $this->add_control('better_payment_email_from_customer', [
            'label' => __('From email', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_from_email_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_from_email_customer']) : "wordpress@$better_domain",
            'render_type' => 'none',
            'separator' => 'before'
        ]);

        $this->add_control('better_payment_email_from_name_customer', [
            'label' => __('From name', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_from_name_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_from_name_customer']) : get_bloginfo('name'),
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_reply_to_customer', [
            'label' => __('Reply-To', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_reply_to_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_reply_to_customer']) : "wordpress@$better_domain",
            'placeholder' => "wordpress@$better_domain",
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_cc_customer', [
            'label' => __('Cc', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_cc_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_cc_customer']) : '',
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_bcc_customer', [
            'label' => __('Bcc', 'better-payment'),
            'type' => Controls_Manager::TEXT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_bcc_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_bcc_customer']) : '',
            'render_type' => 'none'
        ]);

        $this->add_control('better_payment_email_content_type_customer', [
            'label' => __('Send as', 'better-payment'),
            'type' => Controls_Manager::SELECT,
            'default' => !empty($this->better_payment_global_settings['better_payment_settings_general_email_send_as_customer']) ? esc_html($this->better_payment_global_settings['better_payment_settings_general_email_send_as_customer']) : 'html', //html or plain
            'render_type' => 'none',
            'options' => [
                'html' => __('HTML', 'better-payment'),
                'plain' => __('Plain', 'better-payment')
            ]
        ]);

		$this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function success_message_setting() {
        $this->start_controls_section(
            'better_payment_form_success_message_settings',
            [
                'label'      => esc_html__( 'Success Message', 'better-payment' ),
                'conditions' => [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'name'  => 'better_payment_form_stripe_enable',
                            'value' => 'yes',
                        ],
                        [
                            'name'  => 'better_payment_form_paypal_enable',
                            'value' => 'yes',
                        ],
                    ],
                ]
            ]
        );

        $this->add_control(
            'better_payment_form_success_message_icon',
            [
                'label' => esc_html__( 'Icon', 'better-payment' ),
                'type'  => Controls_Manager::ICONS,

            ]
        );

        $this->add_control(
            'better_payment_form_success_message_heading',
            [
                'label'       => __( 'Heading Message Text', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Payment Complete', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->add_control(
            'better_payment_form_success_message_transaction',
            [
                'label'       => __( 'Transaction Text', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Transaction Number', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->add_control(
            'better_payment_form_success_message_thanks',
            [
                'label'       => __( 'Thanks Text', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Thank you for donation.', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->end_controls_section();
    }

    public function error_message_setting() {
        $this->start_controls_section(
            'better_payment_form_error_message_settings',
            [
                'label'      => esc_html__( 'Error Message', 'better-payment' ),
                'conditions' => [
                    'relation' => 'or',
                    'terms'    => [
                        [
                            'name'  => 'better_payment_form_stripe_enable',
                            'value' => 'yes',
                        ],
                        [
                            'name'  => 'better_payment_form_paypal_enable',
                            'value' => 'yes',
                        ],
                    ],
                ]
            ]
        );

        $this->add_control(
            'better_payment_form_error_message_icon',
            [
                'label' => esc_html__( 'Icon', 'better-payment' ),
                'type'  => Controls_Manager::ICONS,

            ]
        );

        $this->add_control(
            'better_payment_form_error_message_heading',
            [
                'label'       => __( 'Heading Message Text', 'better-payment' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => __( 'Payment Failed', 'better-payment' ),
                'dynamic'     => [
                    'active' => true,
                ],
                'label_block' => true
            ]
        );

        $this->end_controls_section();
    }

    public function form_style() {

        $this->header_form_container();

        $this->form_container();

        $this->input_style();

        $this->amount_field_style();

        $this->placeholder_style();

        $this->paypal_button_style();

        $this->stripe_button();

        $this->success_message_style();

        $this->error_message_style();

    }

    public function header_form_container() {
        $this->start_controls_section(
            'header_better_payment_form_settings_style',
            [
                'label' => esc_html__( 'Form Container Header Style', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'header_better_payment_form_topbar_background',
                'label'    => __( 'Top Bar Background', 'better-payment' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before',
            ]
        );

        $this->add_responsive_control(
            'header_better_payment_form_margin',
            [
                'label'      => esc_html__( 'Margin', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'header_better_payment_form_padding',
            [
                'label'      => esc_html__( 'Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'header_better_payment_form_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'header_better_payment_form_border',
                'selector' => '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'header_better_payment_form_box_shadow',
                'selector' => '{{WRAPPER}} .better-payment--container .better-payment-form-layout:before',
            ]
        );

        $this->end_controls_section();
    }

    public function form_container() {
        $this->start_controls_section(
            'better_payment_form_settings_style',
            [
                'label' => esc_html__( 'Form Container', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'better_payment_form_background',
                'label'    => __( 'Background', 'better-payment' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .better-payment-form-layout form',
            ]
        );


        $this->add_responsive_control(
            'better_payment_form_alignment',
            [
                'label'      => esc_html__( 'Form Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_margin',
            [
                'label'      => esc_html__( 'Margin', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_padding',
            [
                'label'      => esc_html__( 'Form Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'better_payment_form_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'better_payment_form_border',
                'selector' => '{{WRAPPER}} .better-payment-form-layout form',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'better_payment_form_box_shadow',
                'selector' => '{{WRAPPER}} .better-payment-form-layout form',
            ]
        );

        $this->end_controls_section();
    }

    public function input_style() {

        $this->start_controls_section(
            'better_payment_form_fields_style',
            [
                'label' => __( 'Form Input Text', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'better_payment_form_fields_input_field_bg',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_form_input_field_text_color',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'color: {{VALUE}}',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_input_spacing',
            [
                'label'      => __( 'Spacing', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'size' => '0',
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_input_field_padding',
            [
                'label'      => __( 'Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_input_text_indent',
            [
                'label'      => __( 'Text Indent', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 60,
                        'step' => 1,
                    ],
                    '%'  => [
                        'min'  => 0,
                        'max'  => 30,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'text-indent: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'text-indent: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_input_width',
            [
                'label'      => __( 'Input Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_input_height',
            [
                'label'      => __( 'Input Height', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'better_payment_form_input_field_border',
                'label'       => __( 'Border', 'better-payment' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .better-payment-form-layout form input[type=text],{{WRAPPER}} .better-payment-form-layout form input[type=email]',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'better_payment_form_input_field_radius',
            [
                'label'      => __( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'better_payment_form_input_field_typography',
                'label'     => __( 'Typography', 'better-payment' ),
                'scheme'    => Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .better-payment-form-layout form input[type=text],{{WRAPPER}} .better-payment-form-layout form input[type=email]',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'better_payment_form_input_field_box_shadow',
                'selector'  => '{{WRAPPER}} .better-payment-form-layout form input[type=text],{{WRAPPER}} .better-payment-form-layout form input[type=email]',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
    }

    public function amount_field_style() {

        $this->start_controls_section(
            'better_payment_form_amount_fields_style',
            [
                'label' => __( 'Form Amount', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_amount_width',
            [
                'label'      => __( 'Input Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'default'    => [
                    'size' => '150',
                    'unit' => 'px',
                ],
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]' => 'width: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label'              => 'width: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_form_amount_height',
            [
                'label'      => __( 'Input Height', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]' => 'height: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label'              => 'height: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->start_controls_tabs( 'better_payment_form_amount_tabs_button_style' );

        $this->start_controls_tab(
            'better_payment_form_amount_tab_button_normal',
            [
                'label' => __( 'Normal', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_form_amount_field_bg',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]' => 'background-color: {{VALUE}}',
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label'              => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_form_amount_text_color',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label'              => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'better_payment_form_amount_tab_button_selected',
            [
                'label' => __( 'Selected', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_form_amount_field_bg_selected',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-form__group input[type="radio"].bp-form__control:checked ~ label' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_form_amount_text_color_selected',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-form__group input[type="radio"].bp-form__control:checked ~ label' => 'color: {{VALUE}}',
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'        => 'better_payment_form_amount_field_border',
                'label'       => __( 'Border', 'better-payment' ),
                'placeholder' => '1px',
                'default'     => '1px',
                'selector'    => '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label,{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]',
                'separator'   => 'before',
            ]
        );

        $this->add_control(
            'better_payment_form_amount_radius',
            [
                'label'      => __( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label'              => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'better_payment_form_amount_field_typography',
                'label'     => __( 'Typography', 'better-payment' ),
                'scheme'    => Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label,{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'better_payment_form_amount_field_box_shadow',
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group label,{{WRAPPER}} .better-payment-form-layout .bp-payment-amount-wrap .bp-form__group input[type=number]',
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
    }

    public function placeholder_style() {

        $this->start_controls_section(
            'better_payment_section_placeholder_style',
            [
                'label' => __( 'Placeholder', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'better_payment_placeholder_switch',
            [
                'label'        => __( 'Show Placeholder', 'better-payment' ),
                'type'         => Controls_Manager::SWITCHER,
                'default'      => 'yes',
                'label_on'     => __( 'Yes', 'better-payment' ),
                'label_off'    => __( 'No', 'better-payment' ),
                'return_value' => 'yes',
            ]
        );

        $this->add_control(
            'better_payment_text_color_placeholder',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout form input[type=text]::-webkit-input-placeholder' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .better-payment-form-layout form input[type=email]::-webkit-input-placeholder' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'better_payment_placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'better_payment_typography_placeholder',
                'label'     => __( 'Typography', 'better-payment' ),
                'scheme'    => Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .better-payment-form-layout form input[type=text]::-webkit-input-placeholder,{{WRAPPER}} .better-payment-form-layout form input[type=email]::-webkit-input-placeholder',
                'condition' => [
                    'better_payment_placeholder_switch' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    public function paypal_button_style() {

        $this->start_controls_section(
            'better_payment_paypal_button_style',
            [
                'label' => __( 'PayPal Button', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'better_payment_paypal_button_align',
            [
                'label'     => __( 'Alignment', 'better-payment' ),
                'type'      => Controls_Manager::CHOOSE,
                'default'   => 'center',
                'options'   => [
                    'flex-start' => [
                        'title' => __( 'Left', 'better-payment' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Center', 'better-payment' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Right', 'better-payment' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .better_payment__form--one form .payment__option' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'better_payment_form_stripe_enable!' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_paypal_button_width',
            [
                'label'      => __( 'Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 700,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better_payment__form--one form .payment__option .better-payment-paypal-bt' => 'flex: none;width: {{SIZE}}{{UNIT}}',
                ],
                'condition'  => [
                    'better_payment_form_stripe_enable!' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs( 'better_payment_paypal_tabs_button_style' );

        $this->start_controls_tab(
            'better_payment_paypal_tab_button_normal',
            [
                'label' => __( 'Normal', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_bg_color_normal',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_text_color_normal',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_paypal_button_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1000,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'better_payment_paypal_button_border_normal',
                'label'    => __( 'Border', 'better-payment' ),
                'default'  => '1px',
                'selector' => '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt',
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_paypal_button_padding',
            [
                'label'      => __( 'Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_paypal_button_margin',
            [
                'label'      => __( 'Margin Top', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'better_payment_paypal_button_typography',
                'label'     => __( 'Typography', 'better-payment' ),
                'scheme'    => Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'better_payment_paypal_button_box_shadow',
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'better_payment_paypal_tab_button_hover',
            [
                'label' => __( 'Hover', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_bg_color_hover',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_text_color_hover',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_paypal_button_border_color_hover',
            [
                'label'     => __( 'Border Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-paypal-bt:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function stripe_button() {

        $this->start_controls_section(
            'better_payment_stripe_button_style',
            [
                'label' => __( 'Stripe Button', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'better_payment_stripe_button_align',
            [
                'label'     => __( 'Alignment', 'better-payment' ),
                'type'      => Controls_Manager::CHOOSE,
                'default'   => 'center',
                'options'   => [
                    'flex-start' => [
                        'title' => __( 'Left', 'better-payment' ),
                        'icon'  => 'eicon-h-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Center', 'better-payment' ),
                        'icon'  => 'eicon-h-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Right', 'better-payment' ),
                        'icon'  => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .better_payment__form--one form .payment__option' => 'justify-content: {{VALUE}};',
                ],
                'condition' => [
                    'better_payment_form_paypal_enable!' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_stripe_button_width',
            [
                'label'      => __( 'Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 1200,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better_payment__form--one form .payment__option .better-payment-stripe-bt' => 'flex: none;width: {{SIZE}}{{UNIT}}',
                ],
                'condition'  => [
                    'better_payment_form_paypal_enable!' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs( 'better_payment_stripe_tabs_button_style' );

        $this->start_controls_tab(
            'better_payment_stripe_tab_button_normal',
            [
                'label' => __( 'Normal', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_bg_color_normal',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_text_color_normal',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_stripe_button_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1000,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'better_payment_stripe_button_border_normal',
                'label'    => __( 'Border', 'better-payment' ),
                'default'  => '1px',
                'selector' => '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt',
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_stripe_button_padding',
            [
                'label'      => __( 'Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_stripe_button_margin',
            [
                'label'      => __( 'Margin Top', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'range'      => [
                    'px' => [
                        'min'  => 0,
                        'max'  => 100,
                        'step' => 1,
                    ],
                ],
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'      => 'better_payment_stripe_button_typography',
                'label'     => __( 'Typography', 'better-payment' ),
                'scheme'    => Typography::TYPOGRAPHY_4,
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt',
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'      => 'better_payment_stripe_button_box_shadow',
                'selector'  => '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'better_payment_stripe_tab_button_hover',
            [
                'label' => __( 'Hover', 'better-payment' ),
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_bg_color_hover',
            [
                'label'     => __( 'Background Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_text_color_hover',
            [
                'label'     => __( 'Text Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt:hover' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'better_payment_stripe_button_border_color_hover',
            [
                'label'     => __( 'Border Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} .better-payment-form-layout .better-payment-stripe-bt:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    public function success_message_style() {

        $this->start_controls_section(
            'better_payment_section_success_style',
            [
                'label' => __( 'Success Message', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'better_payment_success_background',
                'label'    => __( 'Background', 'better-payment' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .better-payment-success-report',
            ]
        );


        $this->add_responsive_control(
            'better_payment_success_alignment',
            [
                'label'      => esc_html__( 'Form Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_margin',
            [
                'label'      => esc_html__( 'Margin', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_padding',
            [
                'label'      => esc_html__( 'Form Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'better_payment_success_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'better_payment_success_border',
                'selector' => '{{WRAPPER}} .better-payment-success-report',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'better_payment_success_box_shadow',
                'selector' => '{{WRAPPER}} .better-payment-success-report',
            ]
        );

        // Success Icon Style : Starts 
        $this->add_control(
            'better_payment_success_icon_style',
            [
                'label'            => __('Icon Style', 'better-payment'),
                'type'             => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_icon_margin',
            [
                'label'      => __('Margin', 'better-payment'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report .report__thumb' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_icon_padding',
            [
                'label'      => __('Padding', 'better-payment'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report .report__thumb' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_icon_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1000,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report .report__thumb img' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_success_icon_font_size',
            [
                'label'      => esc_html__( 'Icon Font Size', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-success-report .report__thumb i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        // Success Icon Style : Ends 

        $this->add_control(
            'better_payment_success_heading_style',
            [
                'label'     => esc_html__( 'Heading Style', 'better-payment' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'better_payment_success_heading_color',
            [
                'label'     => __( 'Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .better-payment-success-report .transaction__success' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'better_payment_success_heading_typography',
                'label'    => __( 'Typography', 'better-payment' ),
                'scheme'   => Typography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .better-payment-success-report .transaction__success',
            ]
        );


        $this->add_control(
            'better_payment_success_transaction_style',
            [
                'label'     => esc_html__( 'Transaction Style', 'better-payment' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'better_payment_success_transaction_color',
            [
                'label'     => __( 'Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .better-payment-success-report .transaction__number' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'better_payment_success_transaction_typography',
                'label'    => __( 'Typography', 'better-payment' ),
                'scheme'   => Typography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .better-payment-success-report .transaction__number',
            ]
        );

        $this->add_control(
            'better_payment_success_thank_you_style',
            [
                'label'     => esc_html__( 'Thank You Message', 'better-payment' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'better_payment_success_thank_you_color',
            [
                'label'     => __( 'Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .better-payment-success-report .payment__greeting' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'better_payment_success_thank_you_typography',
                'label'    => __( 'Typography', 'better-payment' ),
                'scheme'   => Typography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .better-payment-success-report .payment__greeting',
            ]
        );

        $this->end_controls_section();
    }

    public function error_message_style() {

        $this->start_controls_section(
            'better_payment_section_error_style',
            [
                'label' => __( 'Error Message', 'better-payment' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'better_payment_error_background',
                'label'    => __( 'Background', 'better-payment' ),
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .better-payment-error-report',
            ]
        );


        $this->add_responsive_control(
            'better_payment_error_alignment',
            [
                'label'      => esc_html__( 'Form Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1500,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 80,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_margin',
            [
                'label'      => esc_html__( 'Margin', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_padding',
            [
                'label'      => esc_html__( 'Form Padding', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'better_payment_error_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'better-payment' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'separator'  => 'before',
                'size_units' => [ 'px' ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'better_payment_error_border',
                'selector' => '{{WRAPPER}} .better-payment-error-report',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'better_payment_error_box_shadow',
                'selector' => '{{WRAPPER}} .better-payment-error-report',
            ]
        );

        // Error Icon Style : Starts 
        $this->add_control(
            'better_payment_error_icon_style',
            [
                'label'            => __('Icon Style', 'better-payment'),
                'type'             => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_icon_margin',
            [
                'label'      => __('Margin', 'better-payment'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report .report__thumb' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_icon_padding',
            [
                'label'      => __('Padding', 'better-payment'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report .report__thumb' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_icon_max_width',
            [
                'label'      => esc_html__( 'Max Width', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 1000,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report .report__thumb img' => 'max-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'better_payment_error_icon_font_size',
            [
                'label'      => esc_html__( 'Icon Font Size', 'better-payment' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range'      => [
                    'px' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .better-payment-error-report .report__thumb i' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        // Error Icon Style : Ends

        $this->add_control(
            'better_payment_error_heading_style',
            [
                'label'     => esc_html__( 'Heading Style', 'better-payment' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'better_payment_error_heading_color',
            [
                'label'     => __( 'Color', 'better-payment' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .better-payment-error-report .transaction__failed' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'better_payment_error_heading_typography',
                'label'    => __( 'Typography', 'better-payment' ),
                'scheme'   => Typography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} .better-payment-error-report .transaction__failed',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {

        $settings = $this->get_settings_for_display();

        if ( $settings[ 'better_payment_form_paypal_enable' ] != 'yes' && $settings[ 'better_payment_form_stripe_enable' ] != 'yes' ) {
            return false;
        }

        $field_arr = [
            'first_name',
            'last_name',
            'email'
        ];

        $payment_field = "number" ;

        $response = Better_Payment_Handler::manage_response( $settings );
        if ( $response ) {
            return false;
        }

        if(
            isset($_REQUEST[ 'better_payment_error_status' ]) ||
            isset($_REQUEST[ 'better_payment_stripe_status' ]) ||
            isset($_REQUEST[ 'better_payment_paypal_status' ])
        ){
            //Hide form : when page has success/error message
            return false;
        }

        wp_enqueue_script( 'better-payment' );
        $action       = esc_url( admin_url( 'admin-post.php' ) );
        $setting_meta = json_encode( [
            'page_id'   => get_the_ID(),
            'widget_id' => esc_attr( $this->get_id() ),
        ] );
        
        $better_payment_placeholder_class = '';
        if ( $settings[ 'better_payment_placeholder_switch' ] != 'yes' ) {
            $better_payment_placeholder_class = 'better-payment-hide-placeholder';
        }

        ?>
        <div class="better-payment--wrapper">
            <div class="better-payment--container">
                <div class="better_payment__form--one better-payment-form-layout <?php echo esc_attr($better_payment_placeholder_class); ?>" >
                    <form name="better-payment-form-<?php echo esc_attr( $this->get_id() ); ?>"
                          data-better-payment="<?php echo esc_attr( $setting_meta ); ?>"
                          class="better-payment-form" id="better-payment-form-<?php echo esc_attr( $this->get_id() ); ?>"
                          action="<?php echo esc_url( $action ); ?>" method="post">
                        <input type="hidden" name="better_payment_page_id" value="<?php echo get_the_ID(); ?>">
                        <input type="hidden" name="better_payment_widget_id" value="<?php echo esc_attr( $this->get_id() ); ?>">
                        <?php
                        foreach ( $field_arr as $item ):
                            if ( $settings[ "better_payment_{$item}_show" ] != 'yes' ) {
                                continue;
                            }
                            $is_item_required = $settings[ "better_payment_{$item}_required" ]== 'yes' ? 1 : 0;
                            $required_class = $is_item_required ? ' required' : '';
                            $required_placeholder = $settings[ "better_payment_{$item}_required" ]== 'yes' ? ' *' : '';

                            $render_attribute_type = ($item=='email') ? 'email':'text';
                            $render_attribute_name = $item;
                            $render_attribute_class = "bp-form__control " . $required_class;
                            $render_attribute_placeholder = $settings[ "better_payment_{$item}_placeholder" ] . $required_placeholder;
                            $render_attribute_required = $settings[ "better_payment_{$item}_required" ] == 'yes' ? 'required' : '';
                            
                            ?>
                            <div class="bp-form__group">
                                <input 
                                    class="bp-form__control <?php echo esc_attr($required_class); ?>" 
                                    type="<?php echo esc_attr($render_attribute_type); ?>"
                                    name="<?php echo esc_attr($render_attribute_name); ?>"
                                    class="<?php echo esc_attr($render_attribute_class); ?>"
                                    placeholder="<?php echo esc_attr($render_attribute_placeholder); ?>"
                                    
                                    <?php if($render_attribute_required) : ?>
                                    required="<?php echo esc_attr($render_attribute_required); ?>"
                                    <?php endif; ?>

                                >
                            </div>
                        <?php endforeach; ?>

                        <div class="bp-payment-amount-wrap mb30">
                            <?php
                            if ( $settings[ 'better_payment_show_amount_list' ] == 'yes' ) {
                                $this->render_amount_element( $settings );
                            }
                            ?>

                            <div class="bp-form__group">
                                <input type="<?php echo esc_attr($payment_field); ?>" name="pay_amount"
                                       class="bp-form__control bp-custom-pay-amount"
                                       placeholder="" required min="1">
                            </div>
                        </div>

                        <div class="payment__option">
                            <?php

                            if ( $settings[ 'better_payment_form_paypal_enable' ] == 'yes' ) {
                                echo Better_Payment_Handler::paypal_button( esc_attr( $this->get_id() ), $settings );
                            }

                            if ( $settings[ 'better_payment_form_stripe_enable' ] == 'yes' ) {
                                echo Better_Payment_Handler::stripe_button();
                            }

                            ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_amount_element( $settings ) {
        $id = esc_attr( $this->get_id() );
        foreach ( $settings[ 'better_payment_amount' ] as $item ) {
            $uid = uniqid();
            ?>
            <div class="bp-form__group">
                <input type="radio" value="<?php echo floatval( $item[ 'better_payment_amount_val' ] ); ?>"
                       id="bp_pay_amount-<?php echo esc_attr($uid); ?>" class="bp-form__control bp-form_pay-radio "
                       name="pay_amount[<?php echo esc_attr($id); ?>]">
                <label for="bp_pay_amount-<?php echo esc_attr($uid); ?>"><?php printf( "%s%s", Better_Payment_Handler::get_currency_symbols( esc_html($settings[ 'better_payment_form_currency' ]) ), floatval( $item[ 'better_payment_amount_val' ] ) ); ?></label>
            </div>
            <?php
        }
    }

}
