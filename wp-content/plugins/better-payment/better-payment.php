<?php
/**
 * Plugin Name: Better Payment
 * Description: Make instant payment with one click through Better Payment. It offers seamless integrations with Elementor and allows you to get payment through PayPal and Stripe.
 * Plugin URI: https://wpdeveloper.net/
 * Author: WPDeveloper
 * Version: 0.0.2
 * Author URI: https://wpdeveloper.net/
 * Text Domain: better-payment
 * Domain Path: /languages
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

class Better_Payment {

    private static $instance;

    public static function init() {
        if ( !isset( self::$instance ) && !( self::$instance instanceof Better_Payment ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        $this->define_constants();
        $this->init_plugin();

        if (is_admin()) {
            if ( defined( 'ELEMENTOR_VERSION' ) ) {
                new Better_Payment_Settings();
            }

            require 'admin/includes/class-better-payment-helper.php';

            if (!did_action('elementor/loaded')) {
                $notice = new Better_Payment_Helper();
                $notice->elementor_not_loaded();
            }

            if (did_action('elementor/loaded')) {
                add_filter('plugin_action_links', array($this, 'plugin_actions_links'), 10, 2);
            }
        }
    }

    public function define_constants() {
        define( 'BETTER_PAYMENT_VERSION', '0.0.2' );
        define( 'BETTER_PAYMENT_FILE', __FILE__ );
        define( 'BETTER_PAYMENT_BASENAME', plugin_basename( __FILE__ ) );
        define( 'BETTER_PAYMENT_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'BETTER_PAYMENT_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
        define( 'BETTER_PAYMENT_ADMIN_PATH', BETTER_PAYMENT_PATH.'admin/');
        define( 'BETTER_PAYMENT_ADMIN_INCLUDES_PATH', BETTER_PAYMENT_PATH.'admin/includes/');
        define( 'BETTER_PAYMENT_ADMIN_VIEW_PATH', BETTER_PAYMENT_PATH.'admin/views/');
        define( 'BETTER_PAYMENT_ADMIN_URL', BETTER_PAYMENT_URL.'admin/');
        define( 'BETTER_PAYMENT_ADMIN_INCLUDES_URL', BETTER_PAYMENT_URL.'admin/includes/');
        define( 'BETTER_PAYMENT_ADMIN_VIEW_URL', BETTER_PAYMENT_URL.'admin/views/');
        define( 'BETTER_PAYMENT_ADMIN_ASSET_URL', BETTER_PAYMENT_URL.'admin/asset/');

    }

    public function enqueue_scripts() {
        wp_register_script( 'better-payment-stripe', 'https://js.stripe.com/v3/' );
        wp_register_script( 'better-payment', BETTER_PAYMENT_URL . 'asset/js/better-payment.min.js', [
            'jquery',
            'better-payment-stripe'
        ], BETTER_PAYMENT_VERSION, true );
        wp_register_style( 'better-payment-el', BETTER_PAYMENT_URL . 'asset/css/better-payment-el.min.css' );
        wp_localize_script( 'better-payment', 'betterPayment', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'better-payment' ),
        ) );

        wp_enqueue_style( 'toastr-css', BETTER_PAYMENT_URL . 'asset/vendor/toastr/css/toastr.min.css' );
        wp_enqueue_script( 'toastr-js', BETTER_PAYMENT_URL . 'asset/vendor/toastr/js/toastr.min.js', array( 'jquery' ), BETTER_PAYMENT_VERSION, true );

        do_action('better_payment/frontend/after_enqueue_scripts');
    }

    /**
     * Activate plugin.
     *
     * @return void
     * @since 0.0.2
     */
    public function activate_plugin() {
        require 'includes/class-better-payment-installer.php';
        Better_Payment_Installer::install();
    }

    public function init_plugin() {
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            require_once 'includes/class-better-payment-handler.php';
            require_once 'includes/class-better-payment-actions.php';
            require_once 'includes/elementor/class-better-payment-el-integration.php';

            $better_action  = new Better_Payment_Actions();
            $el_integration = new Better_Payment_EL_integration();
            $el_integration->init();

            require_once 'admin/includes/class-better-payment-settings.php';
            require_once 'admin/includes/class-better-payment-db.php';

    		Better_Payment_Settings::save_default_settings();
        }
    }

    /**
	 * Add settings page link on plugins page
	 */
	public function plugin_actions_links($links, $file)
	{
		$better_payment_plugin = plugin_basename(__FILE__);

		if ($file == $better_payment_plugin && current_user_can('manage_options')) {
			$links[] = sprintf('<a href="%s">%s</a>', admin_url("admin.php?page=better-payment-settings"), __('Settings', 'better-payment'));
		}

		return $links;
	}
}

register_activation_hook( __FILE__, 'better_payment_activate' );

function better_payment_activate() {
    require 'includes/class-better-payment-installer.php';
    Better_Payment_Installer::install();
}

add_action( 'plugins_loaded', function () {
    Better_Payment::init();
} );

/**
 * Plugin migrator
 *
 * @since 0.0.2
 */
function better_payment_migrator() {
    require 'includes/class-better-payment-migrator.php';
    Better_Payment_Migrator::migrator();
}

add_action( 'wp_loaded', function () {
    if (get_option('better_payment_version') != BETTER_PAYMENT_VERSION) {
        better_payment_migrator();
        update_option('better_payment_version', BETTER_PAYMENT_VERSION);
    }

    $setup_wizard = get_option( 'better_payment_setup_wizard' );

    require_once 'admin/includes/class-better-payment-settings.php';
    require_once 'admin/includes/class-better-payment-db.php';
    require_once 'admin/includes/class-better-payment-setup-wizard.php';

    if ( $setup_wizard == 'redirect' ) {
        Better_Payment_Setup_Wizard::redirect();
    }

    if ( $setup_wizard == 'init' ) {
        new Better_Payment_Setup_Wizard();
    }
} );