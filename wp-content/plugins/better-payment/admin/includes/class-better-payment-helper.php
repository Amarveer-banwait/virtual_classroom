<?php

/**
 * This class responsible for database work
 * using wordpress functionality 
 * get_option and update_option.
 */
class Better_Payment_Helper
{
    /**
     * Check if elementor plugin is activated
     *
     * @since 0.0.2
     */
    public function elementor_not_loaded()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        if (isset($_GET['page']) && $_GET['page'] == 'better-payment-setup-wizard') {
            return;
        }

        if(wp_doing_ajax()){
            return;
        }

        $elementor = 'elementor/elementor.php';

        if ($this->is_plugin_installed($elementor)) {
            $activation_url = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor);

            $message = sprintf(__('%1$sBetter Payment%2$s requires %1$sElementor%2$s plugin to be active. Please activate Elementor to continue.', 'better-payment'), "<strong>", "</strong>");

            $button_text = __('Activate Elementor', 'better-payment');
        } else {
            $activation_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=elementor'), 'install-plugin_elementor');

            $message = sprintf(__('%1$sBetter Payment%2$s requires %1$sElementor%2$s plugin to be installed and activated. Please install Elementor to continue.', 'better-payment'), '<strong>', '</strong>');
            $button_text = __('Install Elementor', 'better-payment');
        }

        $button = '<p><a href="' . esc_url_raw($activation_url) . '" class="button-primary">' . $button_text . '</a></p>';

        printf('<div class="error"><p>%1$s</p>%2$s</div>', __($message), $button);
    }

    /**
     * Check if a plugin is installed
     *
     * @since 0.0.2
     */
    public function is_plugin_installed($basename)
    {
        if (!function_exists('get_plugins')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugins = get_plugins();

        return isset($plugins[$basename]);
    }

    /**
	 * check is plugin active or not
	 *
	 * @since 0.0.2
	 * @param $plugin
	 * @return bool
	 */
    public function is_plugin_active($plugin) {
	    if ( !function_exists( 'is_plugin_active' ) ){
		    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

	    return is_plugin_active( $plugin );
    }

    public static function sanitize_bp_field($key, $arr, $default_value='', $type='text'){
        $value = $default_value;
        
        if( !empty( $arr[ $key ] ) ) {
            $value = $arr[ $key ];

            switch($type){
                case 'text':
                    $value = sanitize_text_field( $value );
                    break;
                case 'email':
                    $value = sanitize_email( $value );
                    break;
                case 'textarea':
                    $value = sanitize_textarea_field( $value );
                    break;
                default: 
                    $value = sanitize_text_field( $value );
                    break;
            }
        }

        return $value;
    }

    public function get_better_payment_widget_settings( $page_id, $widget_id ) {
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
}
